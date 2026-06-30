<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PoliticalParty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PoliticalPartyController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 12), 50);

        $parties = PoliticalParty::published()
            ->with(['coalitions' => fn ($q) => $q->published()->ordered()])
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('abbreviation', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            })
            ->ordered()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (PoliticalParty $party) => $this->formatParty($party));

        return response()->json($parties);
    }

    public function show(string $slug): JsonResponse
    {
        $party = PoliticalParty::with([
                'coalitions' => fn ($q) => $q->published()->ordered(),
                'newsArticles' => fn ($q) => $q->where('status', 'published')->latest('published_at')->limit(10),
            ])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatParty($party, true),
        ]);
    }

    private function formatParty(PoliticalParty $party, bool $includeContent = false): array
    {
        $data = [
            'id' => $party->id,
            'name' => $party->name,
            'slug' => $party->slug,
            'abbreviation' => $party->abbreviation,
            'logo' => $party->logo,
            'logo_url' => $this->storageUrl($party->logo),
            'brand_color' => $party->brand_color,
            'excerpt' => $party->excerpt,
            'website_url' => $party->website_url,
            'sort_order' => $party->sort_order,
            'coalitions' => $party->coalitions->map(fn ($coalition) => [
                'id' => $coalition->id,
                'name' => $coalition->name,
                'slug' => $coalition->slug,
                'logo_url' => $this->storageUrl($coalition->logo),
                'brand_color' => $coalition->brand_color,
            ])->values(),
        ];

        if ($includeContent) {
            $data['content'] = $party->content;
            $data['news'] = $party->newsArticles->map(fn ($article) => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'featured_image_url' => $this->storageUrl($article->featured_image),
                'published_at' => optional($article->published_at)->toISOString(),
            ])->values();
        }

        return $data;
    }

    private function storageUrl(?string $path): ?string
    {
        return $path ? asset(Storage::url($path)) : null;
    }
}