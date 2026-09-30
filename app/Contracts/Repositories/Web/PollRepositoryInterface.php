<?php

namespace App\Contracts\Repositories\Web;

use App\Models\Poll;
use App\Models\PollComment;
use App\Models\User;
use Illuminate\Support\Collection;

interface PollRepositoryInterface
{
    /**
     * Shells of every open poll, keyed by id, carrying only the audience
     * columns. Light enough to cache and to filter per viewer before any full
     * poll is loaded.
     */
    public function openPollCandidates(): Collection;

    /**
     * Load a poll for display, with its options, candidate details and vote
     * count already eager loaded.
     */
    public function findForDisplay(int $pollId): ?Poll;

    /**
     * Tally for one poll, keyed by option id. Only ever called once results
     * are public, so the raw figures cannot leak before the deadline.
     */
    public function resultsFor(int $pollId): Collection;

    public function votedOptionId(int $pollId, int $userId): ?int;

    public function optionExists(int $pollId, int $optionId): bool;

    public function recordVote(int $pollId, int $optionId, int $userId): void;

    public function approvedComments(int $pollId, int $limit = 20): Collection;

    public function approvedCommentCount(int $pollId): int;

    public function saveComment(Poll $poll, User $user, string $body): PollComment;
}
