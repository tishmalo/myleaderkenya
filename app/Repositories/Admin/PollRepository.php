<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\PollRepositoryInterface;
use App\Models\Candidate;
use App\Models\Poll;
use App\Models\PollComment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PollRepository implements PollRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Poll::query()
            ->withCount('votes')
            // Counted here rather than per row in the Blade, which used to
            // fire one extra query for every poll on the page.
            ->withCount('options')
            ->withCount(['comments as pending_comments_count' => fn ($query) => $query->where('status', PollComment::STATUS_PENDING)])
            ->latest()
            ->paginate($perPage);
    }

    public function find(int $id): ?Poll
    {
        return Poll::query()
            ->with(['options.candidate.position', 'options.candidate.politicalParty'])
            ->withCount('votes')
            ->find($id);
    }

    /**
     * Candidates keyed by id, used to resolve the labels an admin submitted
     * and to reject ids that no longer point at a real candidate.
     */
    public function candidatesByIds(array $ids): Collection
    {
        if ($ids === []) {
            return new Collection;
        }

        return Candidate::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Poll::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    /**
     * Demotes every other live poll. The homepage shows a single poll, so a
     * second active one would simply be invisible.
     */
    public function closeOtherActivePolls(?int $ignoreId = null): void
    {
        Poll::query()
            ->active()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->update(['status' => Poll::STATUS_CLOSED]);
    }

    public function resultsFor(int $pollId): Collection
    {
        $poll = Poll::query()
            ->with('options')
            ->find($pollId);

        if ($poll === null) {
            return new Collection;
        }

        $tallies = DB::table('poll_votes')
            ->select('poll_option_id', DB::raw('COUNT(*) as aggregate'))
            ->where('poll_id', $pollId)
            ->groupBy('poll_option_id')
            ->pluck('aggregate', 'poll_option_id');

        $total = (int) $tallies->sum();

        return collect($poll->options)->map(fn ($option) => [
            'option_id' => (int) $option->id,
            'label' => $option->label,
            'votes' => (int) ($tallies[$option->id] ?? 0),
            'percent' => $total > 0
                ? (int) round(((int) ($tallies[$option->id] ?? 0)) / $total * 100)
                : 0,
        ])->values();
    }

    public function create(array $data, array $options): Poll
    {
        return DB::transaction(function () use ($data, $options) {
            $poll = Poll::create($data);

            $this->syncOptions($poll, $options);

            return $poll->load('options.candidate.position');
        });
    }

    public function update(Poll $poll, array $data, array $options): Poll
    {
        return DB::transaction(function () use ($poll, $data, $options) {
            $poll->update($data);

            $this->syncOptions($poll, $options);

            return $poll->load('options.candidate.position');
        });
    }

    public function delete(Poll $poll): void
    {
        $poll->delete();
    }

    /**
     * Reconciles submitted option rows against what is stored.
     *
     * Rows that still exist are updated in place so existing votes keep
     * pointing at the right option; removed rows are deleted (cascading their
     * votes) and new ones are appended in submitted order.
     */
    protected function syncOptions(Poll $poll, array $options): void
    {
        $keepIds = [];

        foreach ($options as $index => $option) {
            $label = trim((string) ($option['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $attributes = [
                'label' => $label,
                'candidate_id' => $option['candidate_id'] ?? null,
                'display_order' => $index,
            ];

            $existing = isset($option['id']) && $option['id'] !== null
                ? $poll->options()->whereKey((int) $option['id'])->first()
                : null;

            if ($existing) {
                $existing->update($attributes);
                $keepIds[] = $existing->id;

                continue;
            }

            $keepIds[] = $poll->options()->create($attributes)->id;
        }

        $poll->options()->whereNotIn('id', $keepIds ?: [0])->delete();
    }

    public function comments(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return PollComment::query()
            ->with(['user', 'poll'])
            ->when(
                in_array($filters['status'] ?? null, [PollComment::STATUS_PENDING, PollComment::STATUS_APPROVED, PollComment::STATUS_REJECTED], true),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->when($filters['poll_id'] ?? null, fn ($query) => $query->where('poll_id', $filters['poll_id']))
            ->when($filters['search'] ?? null, function ($query) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('body', 'like', $term)
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $term))
                        ->orWhereHas('poll', fn ($poll) => $poll->where('question', 'like', $term));
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function setCommentStatus(PollComment $comment, string $status, int $moderatorId): PollComment
    {
        $comment->update([
            'status' => $status,
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
        ]);

        return $comment;
    }

    public function deleteComment(PollComment $comment): void
    {
        $comment->delete();
    }
}
