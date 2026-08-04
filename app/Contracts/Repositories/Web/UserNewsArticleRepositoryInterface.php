<?php

namespace App\Contracts\Repositories\Web;

use App\Models\NewsArticle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserNewsArticleRepositoryInterface
{
    public function paginateForAuthor(int $authorId, int $perPage = 12): LengthAwarePaginator;
    public function paginateForCandidate(int $candidateId, int $perPage = 12): LengthAwarePaginator;
    public function create(array $data): NewsArticle;
    public function slugExists(string $slug): bool;
    public function allTags(): Collection;
    public function searchCandidates(string $term, int $limit = 20): Collection;
    public function findCandidatesByIds(array $candidateIds): Collection;
    public function syncTags(NewsArticle $article, array $tagIds): void;
    public function syncCandidates(NewsArticle $article, array $candidateIds): void;
}
