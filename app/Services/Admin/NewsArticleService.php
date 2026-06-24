<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\NewsArticleRepositoryInterface;
use App\Models\NewsArticle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsArticleService
{
    public function __construct(
        private NewsArticleRepositoryInterface $newsArticleRepository
    ) {}

    public function getPaginatedArticles(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->newsArticleRepository->paginate($filters, $perPage);
    }

    public function getFormData(): array
    {
        return [
            'categories' => $this->newsArticleRepository->allCategories(),
            'candidates' => $this->newsArticleRepository->allCandidates(),
        ];
    }

    public function createArticle(array $data, ?UploadedFile $featuredImage = null, array $categories = [], array $candidates = []): NewsArticle
    {
        if ($featuredImage) {
            $data['featured_image'] = $featuredImage->store('news', 'public');
        }

        $data['author_id'] = auth()->id();
        $data['slug'] = Str::slug($data['title']);

        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        $article = $this->newsArticleRepository->create($data);

        $this->newsArticleRepository->syncCategories($article, $categories);
        $this->newsArticleRepository->syncCandidates($article, $candidates);

        return $article;
    }

    public function updateArticle(NewsArticle $article, array $data, ?UploadedFile $featuredImage = null, array $categories = [], array $candidates = []): bool
    {
        if ($featuredImage) {
            if ($article->featured_image) {
                Storage::disk('public')->delete($article->featured_image);
            }
            $data['featured_image'] = $featuredImage->store('news', 'public');
        }

        $data['slug'] = Str::slug($data['title']);

        if ($data['status'] === 'published' && !$article->published_at) {
            $data['published_at'] = now();
        }

        $success = $this->newsArticleRepository->update($article, $data);

        $this->newsArticleRepository->syncCategories($article, $categories);
        $this->newsArticleRepository->syncCandidates($article, $candidates);

        return $success;
    }

    public function deleteArticle(NewsArticle $article): bool
    {
        if ($article->featured_image) {
            Storage::disk('public')->delete($article->featured_image);
        }

        return $this->newsArticleRepository->delete($article);
    }

    public function getPublicShowData(string $slug): NewsArticle
    {
        return $this->newsArticleRepository->findBySlug($slug, true);
    }
}
