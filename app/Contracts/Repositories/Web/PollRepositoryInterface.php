<?php

namespace App\Contracts\Repositories\Web;

use App\Models\Poll;
use App\Models\PollComment;
use App\Models\User;
use Illuminate\Support\Collection;

interface PollRepositoryInterface
{
    /**
     * Id of the single poll eligible for the homepage section, or null.
     *
     * Kept separate from the full load so the caller can cache this cheap
     * lookup while still reading fresh relations.
     */
    public function activePollId(): ?int;

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
