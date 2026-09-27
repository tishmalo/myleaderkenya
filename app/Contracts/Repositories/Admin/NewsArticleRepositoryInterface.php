<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\NewsArticle;
use App\Models\NewsArticleComment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface NewsArticleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findBySlug(string $slug, bool $publishedOnly = true): NewsArticle;

    public function create(array $data): NewsArticle;

    public function update(NewsArticle $article, array $data): bool;

    public function delete(NewsArticle $article): bool;

    public function syncTags(NewsArticle $article, array $tagIds): void;

    public function syncCandidates(NewsArticle $article, array $candidateIds): void;

    public function syncPoliticalParties(NewsArticle $article, array $partyIds): void;

    public function allTags(): Collection;

    public function allCandidates(): Collection;

    public function allPoliticalParties(): Collection;

    public function publicTagsWithCounts(int $limit = 12): Collection;

    public function createComment(NewsArticle $article, User $user, string $body): NewsArticleComment;

    public function approvedCommentsFor(NewsArticle $article): Collection;

    public function approvedCommentCountFor(NewsArticle $article): int;

    public function paginateComments(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function setCommentStatus(NewsArticleComment $comment, string $status, User $moderator): NewsArticleComment;

    public function deleteComment(NewsArticleComment $comment): bool;
}

