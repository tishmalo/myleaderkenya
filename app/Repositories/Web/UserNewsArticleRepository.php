<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\UserNewsArticleRepositoryInterface;
use App\Models\Candidate;
use App\Models\NewsArticle;
use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserNewsArticleRepository implements UserNewsArticleRepositoryInterface
{
    public function paginateForAuthor(int $authorId, int $perPage = 12): LengthAwarePaginator
    {
        return NewsArticle::query()
            ->with(['tags:id,name,slug', 'candidates:id,name,nick_name'])
            ->where('author_id', $authorId)
            ->latest()
            ->paginate($perPage);
    }

    public function paginateForCandidate(int $candidateId, int $perPage = 12): LengthAwarePaginator
    {
        return NewsArticle::query()
            ->with(['author:id,name', 'tags:id,name,slug'])
            ->whereHas('candidates', fn ($query) => $query->whereKey($candidateId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): NewsArticle
    {
        return NewsArticle::create($data);
    }

    public function slugExists(string $slug): bool
    {
        return NewsArticle::where('slug', $slug)->exists();
    }

    public function allTags(): Collection
    {
        return Tag::query()->orderBy('name')->get(['id', 'name']);
    }

    public function searchCandidates(string $term, int $limit = 20): Collection
    {
        return Candidate::query()
            ->select(['id', 'name', 'nick_name'])
            ->where('approval_status', 'approved')
            ->where(function ($query) use ($term): void {
                $query->where('name', 'like', '%'.$term.'%')
                    ->orWhere('nick_name', 'like', '%'.$term.'%');
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function findCandidatesByIds(array $candidateIds): Collection
    {
        if ($candidateIds === []) {
            return collect();
        }

        return Candidate::query()
            ->select(['id', 'name', 'nick_name'])
            ->whereIn('id', $candidateIds)
            ->get();
    }

    public function syncTags(NewsArticle $article, array $tagIds): void
    {
        $article->tags()->sync($tagIds);
    }

    public function syncCandidates(NewsArticle $article, array $candidateIds): void
    {
        $article->candidates()->sync($candidateIds);
    }
}
