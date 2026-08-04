<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\UserNewsArticleRepositoryInterface;
use App\Models\Candidate;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UserNewsArticleService
{
    public function __construct(private UserNewsArticleRepositoryInterface $articles) {}

    public function listFor(User $user, int $perPage = 12): LengthAwarePaginator
    {
        return $this->articles->paginateForAuthor($user->getKey(), $perPage);
    }

    public function formData(array $selectedCandidateIds = []): array
    {
        $candidateIds = array_values(array_unique(array_map('intval', $selectedCandidateIds)));

        return [
            'tags' => $this->articles->allTags(),
            'selectedCandidates' => $this->articles->findCandidatesByIds($candidateIds)
                ->map(fn (Candidate $candidate): array => [
                    'value' => $candidate->id,
                    'label' => $this->candidateLabel($candidate),
                ]),
        ];
    }

    public function searchCandidates(string $term): Collection
    {
        return $this->articles->searchCandidates($term)
            ->map(fn (Candidate $candidate): array => [
                'id' => $candidate->id,
                'text' => $this->candidateLabel($candidate),
            ]);
    }

    public function submit(User $user, array $data, ?UploadedFile $image = null): NewsArticle
    {
        $tagIds = array_values(array_unique(array_map('intval', $data['tags'] ?? [])));
        $candidateIds = array_values(array_unique(array_map('intval', $data['candidates'] ?? [])));
        unset($data['tags'], $data['candidates'], $data['featured_image']);

        $data['author_id'] = $user->getKey();
        $data['status'] = 'draft';
        $data['published_at'] = null;
        $data['slug'] = $this->uniqueSlug($data['title']);

        if ($image) {
            $data['featured_image'] = $image->store('news/user-submissions', 'public');
        }

        $article = $this->articles->create($data);
        $this->articles->syncTags($article, $tagIds);
        $this->articles->syncCandidates($article, $candidateIds);

        return $article;
    }

    private function candidateLabel(Candidate $candidate): string
    {
        return trim($candidate->name.($candidate->nick_name ? ' ('.$candidate->nick_name.')' : ''));
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
