<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\NewsArticle;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AspirantController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 12), 50);

        $aspirants = Candidate::with(['position', 'politicalParty'])
            ->when($request->query('featured') !== null, function ($query) use ($request) {
                $query->where('featured', filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN));
            })
            ->when($request->query('county'), fn ($query, $county) => $query->where('county', $county))
            ->when($request->query('constituency'), fn ($query, $constituency) => $query->where('constituency', $constituency))
            ->when($request->query('ward'), fn ($query, $ward) => $query->where('ward', $ward))
            ->when($request->query('position'), function ($query, $position) {
                if (is_numeric($position)) {
                    $query->where('position_id', $position);
                    return;
                }

                $positionAliases = [
                    'presidential' => ['presidential', 'president'],
                    'governor' => ['governor'],
                    'senator' => ['senator'],
                    'women-rep' => ['women rep', 'woman rep', 'women representative', 'woman representative'],
                    'mp' => ['mp', 'member of parliament'],
                    'mca' => ['mca', 'member of county assembly'],
                ];
                $positionKey = strtolower(str_replace('_', '-', trim($position)));
                $names = $positionAliases[$positionKey] ?? [str_replace('-', ' ', $positionKey)];

                $query->whereHas('position', function ($positionQuery) use ($names) {
                    $positionQuery->whereIn($positionQuery->getModel()->getTable() . '.name', $names);
                });
            })
            ->when($request->query('political_party'), function ($query, $party) {
                if (is_numeric($party)) {
                    $query->where('political_party_id', $party);
                    return;
                }

                $query->whereHas('politicalParty', function ($partyQuery) use ($party) {
                    $partyQuery->where('slug', $party)
                        ->orWhere('name', 'like', "%{$party}%")
                        ->orWhere('abbreviation', 'like', "%{$party}%");
                });
            })
            ->when($request->query('candidate') ?? $request->query('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nick_name', 'like', "%{$search}%")
                        ->orWhere('about', 'like', "%{$search}%");
                });
            })
            ->when(! $this->requestTargetsPresidential($request), function ($query) {
                $query->orderByRaw("CASE WHEN county IS NULL OR county = '' THEN 1 ELSE 0 END")
                    ->orderBy('county');
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Candidate $candidate) => $this->formatAspirant($candidate));

        return response()->json($aspirants);
    }

    public function show(Candidate $candidate): JsonResponse
    {
        $candidate->load(['position', 'politicalParty']);

        $relatedArticles = NewsArticle::with('tags')
            ->whereHas('candidates', fn ($query) => $query->where('candidates.id', $candidate->id))
            ->where('status', 'published')
            ->latest('published_at')
            ->take(10)
            ->get();

        return response()->json([
            'data' => array_merge($this->formatAspirant($candidate, true), [
                'related_news' => $relatedArticles->map(fn (NewsArticle $article) => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'featured_image_url' => $this->storageUrl($article->featured_image),
                    'published_at' => optional($article->published_at)->toISOString(),
                    'tags' => $article->tags->map(fn ($tag) => [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                        'color' => $tag->color ?? null,
                    ])->values(),
                ])->values(),
            ]),
        ]);
    }

    private function requestTargetsPresidential(Request $request): bool
    {
        $position = $request->query('position');

        if ($position === null || $position === '') {
            return false;
        }

        if (is_numeric($position)) {
            $position = Position::whereKey($position)->value('name');
        }

        if (! is_string($position)) {
            return false;
        }

        $position = strtolower(str_replace(['_', '-'], ' ', trim($position)));

        return str_contains($position, 'president');
    }

    private function formatAspirant(Candidate $candidate, bool $includeAbout = false): array
    {
        $data = [
            'id' => $candidate->id,
            'name' => $candidate->name,
            'nick_name' => $candidate->nick_name,
            'phone' => $candidate->phone,
            'email' => $candidate->email,
            'featured' => (bool) $candidate->featured,
            'profile_picture' => $candidate->profile_picture,
            'profile_picture_url' => $this->storageUrl($candidate->profile_picture),
            'country' => $this->formatLocationValue($candidate->country),
            'county' => $this->formatLocationValue($candidate->county),
            'constituency' => $this->formatLocationValue($candidate->constituency),
            'ward' => $this->formatLocationValue($candidate->ward),
            'position' => $candidate->position ? [
                'id' => $candidate->position->id,
                'name' => $candidate->position->name,
                'slug' => $candidate->position->slug ?? null,
            ] : null,
            'political_party' => $candidate->politicalParty ? [
                'id' => $candidate->politicalParty->id,
                'name' => $candidate->politicalParty->name,
                'slug' => $candidate->politicalParty->slug,
                'abbreviation' => $candidate->politicalParty->abbreviation,
                'logo_url' => $this->storageUrl($candidate->politicalParty->logo),
                'brand_color' => $candidate->politicalParty->brand_color,
            ] : null,
            'created_at' => optional($candidate->created_at)->toISOString(),
        ];

        if ($includeAbout) {
            $data['about'] = $candidate->about;
        }

        return $data;
    }

    private function formatLocationValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value['name'] ?? $value['label'] ?? null;
        }

        if (is_object($value)) {
            return $value->name ?? $value->label ?? null;
        }

        $value = trim((string) $value);
        if ($value === '[object Object]') {
            return null;
        }

        if (str_starts_with($value, '{')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded['name'] ?? $decoded['label'] ?? null;
            }
        }

        return $value;
    }

    private function storageUrl(?string $path): ?string
    {
        return $path ? asset(Storage::url($path)) : null;
    }
}
