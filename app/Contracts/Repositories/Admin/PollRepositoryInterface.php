<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Poll;
use App\Models\PollComment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PollRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Poll;

    public function approvedCandidates(): Collection;

    /**
     * @param  array<int, int>  $ids
     */
    public function candidatesByIds(array $ids): Collection;

    public function slugExists(string $slug, ?int $ignoreId = null): bool;

    public function closeOtherActivePolls(?int $ignoreId = null): void;

    /**
     * Tally for one poll, covering every option so options with no votes
     * still report zero. Admin only: the public tally is deadline gated.
     */
    public function resultsFor(int $pollId): Collection;

    public function create(array $data, array $options): Poll;

    public function update(Poll $poll, array $data, array $options): Poll;

    public function delete(Poll $poll): void;

    public function comments(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function setCommentStatus(PollComment $comment, string $status, int $moderatorId): PollComment;

    public function deleteComment(PollComment $comment): void;
}
