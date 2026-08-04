<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\UserNewsArticleRepositoryInterface;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class UserNewsArticleService
{
    public function __construct(private UserNewsArticleRepositoryInterface $articles) {}

    public function listFor(User $user, int $perPage = 12): LengthAwarePaginator
    {
        return $this->articles->paginateForAuthor($user->getKey(), $perPage);
    }

    public function formData(): array
    {
        return ['tags' => $this->articles->allTags()];
    }

    public function submit(User $user, array $data, ?UploadedFile $image = null): NewsArticle
    {
        $tagIds = array_values(array_unique(array_map('intval', $data['tags'] ?? [])));
        unset($data['tags'], $data['featured_image']);

        $data['author_id'] = $user->getKey();
        $data['status'] = 'draft';
        $data['sentiment'] = 'neutral';
        $data['published_at'] = null;
        $data['slug'] = $this->uniqueSlug($data['title']);

        if ($image) {
            $data['featured_image'] = $image->store('news/user-submissions', 'public');
        }

        $article = $this->articles->create($data);
        $this->articles->syncTags($article, $tagIds);

        return $article;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'news-article';
        $slug = $base;
        $suffix = 2;

        while ($this->articles->slugExists($slug)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
