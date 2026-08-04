<?php

namespace App\Contracts\Repositories\Web;

use App\Models\NewsArticle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserNewsArticleRepositoryInterface
{
    public function paginateForAuthor(int $authorId, int $perPage = 12): LengthAwarePaginator;
    public function create(array $data): NewsArticle;
    public function slugExists(string $slug): bool;
    public function allTags(): Collection;
    public function syncTags(NewsArticle $article, array $tagIds): void;
}
