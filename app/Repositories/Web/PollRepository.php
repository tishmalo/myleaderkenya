<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\PollRepositoryInterface;
use App\Models\Poll;
use App\Models\PollComment;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PollRepository implements PollRepositoryInterface
{
    /**
     * The shells of every open poll, light enough to cache and filter by
     * audience before any full poll is loaded. Returns rows keyed by id, each
     * exposing the audience columns only.
     */
    public function openPollCandidates(): Collection
    {
        return Poll::query()
            ->where('status', Poll::STATUS_ACTIVE)
            ->orderByDesc('ends_at')
            ->get(['id', 'audience_scope', 'audience_county', 'audience_constituency', 'audience_ward'])
            ->mapWithKeys(fn (Poll $poll) => [$poll->id => [
                'id' => $poll->id,
                'audience_scope' => $poll->audience_scope,
                'audience_county' => $poll->audience_county,
                'audience_constituency' => $poll->audience_constituency,
                'audience_ward' => $poll->audience_ward,
            ]]);
    }

    public function findForDisplay(int $pollId): ?Poll
    {
        return Poll::query()
            ->with('options.candidate.position')
            ->withCount('votes')
            ->find($pollId);
    }

    public function resultsFor(int $pollId): Collection
    {
        $tallies = DB::table('poll_votes')
            ->select('poll_option_id', DB::raw('COUNT(*) as aggregate'))
            ->where('poll_id', $pollId)
            ->groupBy('poll_option_id')
            ->pluck('aggregate', 'poll_option_id');

        $total = (int) $tallies->sum();

        return $tallies->map(fn ($votes, $optionId) => [
            'option_id' => (int) $optionId,
            'votes' => (int) $votes,
            'percent' => $total > 0 ? (int) round(((int) $votes / $total) * 100) : 0,
        ])->values();
    }

    public function votedOptionId(int $pollId, int $userId): ?int
    {
        $optionId = PollVote::query()
            ->where('poll_id', $pollId)
            ->where('user_id', $userId)
            ->value('poll_option_id');

        return $optionId === null ? null : (int) $optionId;
    }

    public function optionExists(int $pollId, int $optionId): bool
    {
        return PollOption::query()
            ->where('poll_id', $pollId)
            ->whereKey($optionId)
            ->exists();
    }

    public function recordVote(int $pollId, int $optionId, int $userId): void
    {
        DB::transaction(function () use ($pollId, $optionId, $userId): void {
            PollVote::updateOrCreate(
                ['poll_id' => $pollId, 'user_id' => $userId],
                ['poll_option_id' => $optionId]
            );
        });
    }

    public function approvedComments(int $pollId, int $limit = 20): Collection
    {
        return PollComment::query()
            ->with('user')
            ->where('poll_id', $pollId)
            ->approved()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function approvedCommentCount(int $pollId): int
    {
        return PollComment::query()
            ->where('poll_id', $pollId)
            ->approved()
            ->count();
    }

    public function saveComment(Poll $poll, User $user, string $body): PollComment
    {
        return $poll->comments()->create([
            'user_id' => $user->id,
            'body' => $body,
            'status' => PollComment::STATUS_PENDING,
        ]);
    }
}
