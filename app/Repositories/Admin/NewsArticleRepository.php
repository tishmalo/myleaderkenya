<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\NewsArticleRepositoryInterface;
use App\Models\Candidate;
use App\Models\NewsArticle;
use App\Models\PoliticalParty;
use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NewsArticleRepository implements NewsArticleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = NewsArticle::with('author', 'tags', 'candidates', 'politicalParties');
        $tagSlug = $filters['tag'] ?? null;

        if (! empty($tagSlug)) {
            $query->whereHas('tags', function ($q) use ($tagSlug) {
                $q->where('slug', $tagSlug);
            });
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
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
        $query = NewsArticle::with('author', 'tags', 'candidates', 'politicalParties')
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

    public function syncTags(NewsArticle $article, array $tagIds): void
    {
        $article->tags()->sync($tagIds);
    }

    public function syncCandidates(NewsArticle $article, array $candidateIds): void
    {
        $article->candidates()->sync($candidateIds);
    }
    public function syncPoliticalParties(NewsArticle $article, array $partyIds): void
    {
        $article->politicalParties()->sync($partyIds);
    }
    public function allTags(): Collection
    {
        return Tag::orderBy('name')->get();
    }

    public function allCandidates(): Collection
    {
        return Candidate::orderBy('name')->get();
    }
    public function allPoliticalParties(): Collection
    {
        return PoliticalParty::orderBy('name')->get();
    }
}

