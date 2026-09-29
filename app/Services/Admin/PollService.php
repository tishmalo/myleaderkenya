<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\PollRepositoryInterface;
use App\Models\Candidate;
use App\Models\Poll;
use App\Models\PollComment;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PollService
{
    public function __construct(private readonly PollRepositoryInterface $repository) {}

    public function paginate(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function find(int $id): ?Poll
    {
        return $this->repository->find($id);
    }

    public function approvedCandidates()
    {
        return $this->repository->approvedCandidates();
    }

    /**
     * Turning a poll live closes any other live poll, because the homepage
     * shows exactly one and a second active poll would be invisible.
     */
    public function create(array $validated, User $author): Poll
    {
        [$data, $options] = $this->shape($validated);

        $this->enforceSingleActivePoll($data['status'], null);

        return $this->repository->create($data + ['created_by' => $author->id], $options);
    }

    public function update(Poll $poll, array $validated): Poll
    {
        [$data, $options] = $this->shape($validated, $poll);

        $this->enforceSingleActivePoll($data['status'], $poll->id);

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
        ];

        $candidateIds = collect($validated['options'] ?? [])
            ->pluck('candidate_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique();

        $candidates = $candidateIds->isEmpty()
            ? collect()
            : Candidate::query()->whereIn('id', $candidateIds)->get()->keyBy('id');

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

        return [$data, $options];
    }

    protected function uniqueSlug(string $question, ?int $ignoreId = null): string
    {
        $base = Str::slug($question) ?: 'poll';
        $slug = $base;
        $suffix = 2;

        while (Poll::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * Only one poll may be live at a time. Activating one demotes any other,
     * so the homepage never has to choose between two live polls.
     */
    protected function enforceSingleActivePoll(string $status, ?int $ignoreId): void
    {
        if ($status !== Poll::STATUS_ACTIVE) {
            return;
        }

        Poll::query()
            ->active()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->update(['status' => Poll::STATUS_CLOSED]);
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
