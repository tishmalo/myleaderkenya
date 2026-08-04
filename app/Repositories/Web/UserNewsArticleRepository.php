<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\UserNewsArticleRepositoryInterface;
use App\Models\NewsArticle;
use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserNewsArticleRepository implements UserNewsArticleRepositoryInterface
{
    public function paginateForAuthor(int $authorId, int $perPage = 12): LengthAwarePaginator
    {
        return NewsArticle::query()
            ->with('tags:id,name,slug')
            ->where('author_id', $authorId)
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

    public function syncTags(NewsArticle $article, array $tagIds): void
    {
        $article->tags()->sync($tagIds);
    }
}
