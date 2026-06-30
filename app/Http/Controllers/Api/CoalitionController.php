<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coalition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CoalitionController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 12), 50);

        $coalitions = Coalition::with(['politicalParties' => fn ($q) => $q->published()->ordered()])
            ->published()
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            })
            ->ordered()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Coalition $coalition) => $this->formatCoalition($coalition));

        return response()->json($coalitions);
    }

    public function show(string $slug): JsonResponse
    {
        $coalition = Coalition::with(['politicalParties' => fn ($q) => $q->published()->ordered()])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatCoalition($coalition, true),
        ]);
    }

    private function formatCoalition(Coalition $coalition, bool $includeContent = false): array
    {
        $data = [
            'id' => $coalition->id,
            'name' => $coalition->name,
            'slug' => $coalition->slug,
            'logo' => $coalition->logo,
            'logo_url' => $this->storageUrl($coalition->logo),
            'brand_color' => $coalition->brand_color,
            'excerpt' => $coalition->excerpt,
            'sort_order' => $coalition->sort_order,
            'political_parties' => $coalition->politicalParties->map(fn ($party) => [
                'id' => $party->id,
                'name' => $party->name,
                'slug' => $party->slug,
                'abbreviation' => $party->abbreviation,
                'logo_url' => $this->storageUrl($party->logo),
                'brand_color' => $party->brand_color,
            ])->values(),
        ];

        if ($includeContent) {
            $data['content'] = $coalition->content;
        }

        return $data;
    }

    private function storageUrl(?string $path): ?string
    {
        return $path ? asset(Storage::url($path)) : null;
    }
}