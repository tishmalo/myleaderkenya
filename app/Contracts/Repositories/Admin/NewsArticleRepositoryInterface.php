<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\NewsArticle;
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
}

