<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Contracts\Repositories\Admin\PollRepositoryInterface;
use App\Models\Candidate;
use App\Models\Poll;
use App\Models\PollComment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PollService
{
    public function __construct(
        private readonly PollRepositoryInterface $repository,
        private readonly CandidateRepositoryInterface $candidates,
    ) {}

    public function paginate(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function find(int $id): ?Poll
    {
        return $this->repository->find($id);
    }

    /**
     * The bulk aspirant picker for political polls. Returns everything a
     * single picker request needs: the position and county lists, the
     * dependent location lists for whatever the admin has chosen, and the
     * approved aspirants that match the current filters.
     */
    public function pickerData(array $filters): array
    {
        $county = $filters['county'] ?? null;
        $constituency = $filters['constituency'] ?? null;

        // Never dump the whole aspirant table: candidates are only listed once
        // the admin has narrowed by position and/or location.
        $inScope = ! empty($filters['position_id'])
            || filled($county)
            || filled($constituency)
            || isset($filters['ward']) && filled($filters['ward']);

        return [
            'positions' => $this->candidates->allPositions(),
            'counties' => $this->candidates->allCounties(),
            'constituencies' => $county ? $this->candidates->allConstituencies($county) : collect(),
            'wards' => $constituency ? $this->candidates->allWards($constituency) : collect(),
            'candidates' => $inScope ? $this->candidates->forPicker($filters, 500) : collect(),
        ];
    }

    public function resultsFor(int $pollId)
    {
        return $this->repository->resultsFor($pollId);
    }

    /**
     * Polls may now run alongside each other: the homepage filters open polls
     * by audience, so several can be live at once.
     */
    public function create(array $validated, User $author): Poll
    {
        [$data, $options] = $this->shape($validated);

        return $this->repository->create($data + ['created_by' => $author->id], $options);
    }

    public function update(Poll $poll, array $validated): Poll
    {
        [$data, $options] = $this->shape($validated, $poll);

        return $this->repository->update($poll, $data, $options);
    }

    public function delete(Poll $poll): void
    {
        $this->repository->delete($poll);
    }

    /**
     * Splits the validated payload into poll columns and option rows.
     *
     * Political options take their label from the linked candidate so the card
     * always shows the current profile name; plain word options keep the label
     * the admin typed.
     */
    protected function shape(array $validated, ?Poll $poll = null): array
    {
        $data = [
            'question' => $validated['question'],
            'poll_type' => $validated['poll_type'],
            'status' => $validated['status'],
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'],
            'reveal_results' => (bool) ($validated['reveal_results'] ?? false),
            'show_results_to_voters' => (bool) ($validated['show_results_to_voters'] ?? true),
        ];

        $candidateIds = collect($validated['options'] ?? [])
            ->pluck('candidate_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique();

        $candidates = $this->repository->candidatesByIds($candidateIds->all());

        $options = [];

        foreach ($validated['options'] ?? [] as $index => $option) {
            $candidateId = isset($option['candidate_id']) && $option['candidate_id'] !== ''
                ? (int) $option['candidate_id']
                : null;

            if ($candidateId !== null && ! $candidates->has($candidateId)) {
                throw ValidationException::withMessages([
                    "options.{$index}.candidate_id" => 'The selected aspirant no longer exists.',
                ]);
            }

            $label = $data['poll_type'] === Poll::TYPE_POLITICAL
                ? ($candidates->get($candidateId)?->name ?? trim((string) ($option['label'] ?? '')))
                : trim((string) ($option['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $options[] = [
                'id' => $option['id'] ?? null,
                'label' => $label,
                'candidate_id' => $candidateId,
            ];
        }

        if (count($options) < 2) {
            throw ValidationException::withMessages([
                'options' => 'A poll needs at least two options.',
            ]);
        }

        // A slug is unique at the database level, so derive one that cannot
        // collide rather than letting the insert fail on a duplicate.
        $data['slug'] = $this->uniqueSlug($data['question'], $poll?->id);

        $data += $this->audienceFor($data['poll_type'], $candidates);

        return [$data, $options];
    }

    /**
     * The audience a poll addresses, derived from the aspirants it is built
     * from rather than a manual pick:
     *
     *   - words polls and any poll with a presidential aspirant are national,
     *     so guests may view them;
     *   - a race whose aspirants are all ward-level (MCA) is scoped to the
     *     ward they share;
     *   - all-constituency aspirants (MP) scope to the constituency;
     *   - everything else scopes to the single county its aspirants share;
     *   - aspirants that cannot agree on one county fall back to "members",
     *     visible to any registered user with a defined location.
     *
     * @param  Collection<int, Candidate>  $candidates
     */
    protected function audienceFor(string $pollType, Collection $candidates): array
    {
        if ($pollType === Poll::TYPE_WORDS || $candidates->isEmpty()) {
            return $this->nationalAudience();
        }

        $hasPresidential = $candidates->contains(fn (Candidate $candidate) => $this->isPresidentialPosition((string) ($candidate->position?->name ?? '')));

        if ($hasPresidential) {
            return $this->nationalAudience();
        }

        $county = $this->sharedValue($candidates, 'county');

        if ($county === null) {
            return $this->membersAudience();
        }

        $allWardLevel = $candidates->every(fn (Candidate $candidate) => $this->isWardLevelPosition((string) ($candidate->position?->name ?? '')));
        $allConstituencyLevel = $allWardLevel
            || $candidates->every(fn (Candidate $candidate) => $this->isConstituencyLevelPosition((string) ($candidate->position?->name ?? '')));

        if ($allWardLevel) {
            $constituency = $this->sharedValue($candidates, 'constituency');
            $ward = $this->sharedValue($candidates, 'ward');

            if ($constituency !== null && $ward !== null) {
                return [
                    'audience_scope' => Poll::AUDIENCE_WARD,
                    'audience_county' => $county,
                    'audience_constituency' => $constituency,
                    'audience_ward' => $ward,
                ];
            }
        }

        if ($allConstituencyLevel) {
            $constituency = $this->sharedValue($candidates, 'constituency');

            if ($constituency !== null) {
                return [
                    'audience_scope' => Poll::AUDIENCE_CONSTITUENCY,
                    'audience_county' => $county,
                    'audience_constituency' => $constituency,
                    'audience_ward' => null,
                ];
            }
        }

        return [
            'audience_scope' => Poll::AUDIENCE_COUNTY,
            'audience_county' => $county,
            'audience_constituency' => null,
            'audience_ward' => null,
        ];
    }

    /**
     * Every aspirant that names this column must name the same value, and at
     * least one must name it, otherwise return null (no shared region).
     *
     * @param  Collection<int, Candidate>  $candidates
     */
    private function sharedValue(Collection $candidates, string $column): ?string
    {
        $values = $candidates->pluck($column)
            ->filter(fn ($value) => filled($value))
            ->unique()
            ->values();

        return $values->count() === 1 ? trim((string) $values->first()) : null;
    }

    private function nationalAudience(): array
    {
        return [
            'audience_scope' => Poll::AUDIENCE_NATIONAL,
            'audience_county' => null,
            'audience_constituency' => null,
            'audience_ward' => null,
        ];
    }

    private function membersAudience(): array
    {
        return [
            'audience_scope' => Poll::AUDIENCE_MEMBERS,
            'audience_county' => null,
            'audience_constituency' => null,
            'audience_ward' => null,
        ];
    }

    private function isPresidentialPosition(string $name): bool
    {
        return str_contains(strtolower($name), 'president');
    }

    private function isMcaPosition(string $name): bool
    {
        return $name === 'mca'
            || str_contains(strtolower($name), 'member of county assembly');
    }

    private function isWardLevelPosition(string $name): bool
    {
        return $this->isMcaPosition($name);
    }

    private function isConstituencyLevelPosition(string $name): bool
    {
        $name = strtolower($name);

        return $this->isMcaPosition($name)
            || $name === 'mp'
            || str_contains($name, 'member of parliament')
            || str_starts_with($name, 'mp ');
    }

    protected function uniqueSlug(string $question, ?int $ignoreId = null): string
    {
        $base = Str::slug($question) ?: 'poll';
        $slug = $base;
        $suffix = 2;

        while ($this->repository->slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function comments(array $filters = [], int $perPage = 25)
    {
        return $this->repository->comments($filters, $perPage);
    }

    public function commentsForPoll(int $pollId, int $perPage = 50)
    {
        return $this->repository->comments(['poll_id' => $pollId], $perPage);
    }

    public function moderateComment(PollComment $comment, string $status, User $moderator): PollComment
    {
        return $this->repository->setCommentStatus($comment, $status, $moderator->id);
    }

    public function deleteComment(PollComment $comment): void
    {
        $this->repository->deleteComment($comment);
    }
}
