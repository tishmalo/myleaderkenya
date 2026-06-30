<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 12), 50);

        $articles = NewsArticle::with(['author:id,name,username', 'tags', 'candidates', 'politicalParties'])
            ->where('status', 'published')
            ->when($request->query('tag'), function ($query, $tag) {
                $query->whereHas('tags', fn ($q) => $q->where('slug', $tag));
            })
            ->when($request->query('party'), function ($query, $party) {
                $query->whereHas('politicalParties', fn ($q) => $q->where('slug', $party));
            })
            ->when($request->query('candidate'), function ($query, $candidate) {
                $query->whereHas('candidates', fn ($q) => $q->where('id', $candidate));
            })
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (NewsArticle $article) => $this->formatArticle($article));

        return response()->json($articles);
    }

    public function show(string $slug): JsonResponse
    {
        $article = NewsArticle::with(['author:id,name,username', 'tags', 'candidates', 'politicalParties'])
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatArticle($article, true),
        ]);
    }

    private function formatArticle(NewsArticle $article, bool $includeContent = false): array
    {
        $data = [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'featured_image' => $article->featured_image,
            'featured_image_url' => $this->storageUrl($article->featured_image),
            'video_url' => $article->video_url,
            'sentiment' => $article->sentiment,
            'published_at' => optional($article->published_at)->toISOString(),
            'created_at' => optional($article->created_at)->toISOString(),
            'author' => $article->author ? [
                'id' => $article->author->id,
                'name' => $article->author->name,
                'username' => $article->author->username,
            ] : null,
            'tags' => $article->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'color' => $tag->color ?? null,
            ])->values(),
            'candidates' => $article->candidates->map(fn ($candidate) => [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'nick_name' => $candidate->nick_name,
                'profile_picture_url' => $this->storageUrl($candidate->profile_picture ?? null),
            ])->values(),
            'political_parties' => $article->politicalParties->map(fn ($party) => $this->formatPartySummary($party))->values(),
        ];

        if ($includeContent) {
            $data['content'] = $article->content;
        }

        return $data;
    }

    private function formatPartySummary($party): array
    {
        return [
            'id' => $party->id,
            'name' => $party->name,
            'slug' => $party->slug,
            'abbreviation' => $party->abbreviation,
            'logo_url' => $this->storageUrl($party->logo),
            'brand_color' => $party->brand_color,
        ];
    }

    private function storageUrl(?string $path): ?string
    {
        return $path ? asset(Storage::url($path)) : null;
    }
}