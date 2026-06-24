<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\NewsArticleRepositoryInterface;
use App\Models\NewsArticle;
use App\Models\Category;
use App\Models\Candidate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NewsArticleRepository implements NewsArticleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = NewsArticle::with('author', 'categories', 'candidates');

        if (!empty($filters['category'])) {
            $query->whereHas('categories', function($q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('content', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['published_only'])) {
            $query->where('status', 'published');
        }

        return $query->latest()->paginate($perPage);
    }

    public function findBySlug(string $slug, bool $publishedOnly = true): NewsArticle
    {
        $query = NewsArticle::with('author', 'categories', 'candidates')
                           ->where('slug', $slug);

        if ($publishedOnly) {
            $query->where('status', 'published');
        }

        return $query->firstOrFail();
    }

    public function create(array $data): NewsArticle
    {
        return NewsArticle::create($data);
    }

    public function update(NewsArticle $article, array $data): bool
    {
        return $article->update($data);
    }

    public function delete(NewsArticle $article): bool
    {
        return $article->delete();
    }

    public function syncCategories(NewsArticle $article, array $categoryIds): void
    {
        $article->categories()->sync($categoryIds);
    }

    public function syncCandidates(NewsArticle $article, array $candidateIds): void
    {
        $article->candidates()->sync($candidateIds);
    }

    public function allCategories(): Collection
    {
        return Category::orderBy('name')->get();
    }

    public function allCandidates(): Collection
    {
        return Candidate::orderBy('name')->get();
    }
}
