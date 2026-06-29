<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Services\Web\LandingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LandingController extends Controller
{
    public function __construct(
        protected LandingService $landingService
    ) {}

    public function index()
    {
        $landingData = $this->landingService->getLandingData();

        return view('landing', $landingData);
    }

    public function featuredAspirants(Request $request)
    {
        $perPage = min(max((int) $request->query('per_page', 20), 2), 40);

        $aspirants = Candidate::query()
            ->with('position')
            ->where('featured', true)
            ->whereNotNull('profile_picture')
            ->where('profile_picture', '!=', '')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $aspirants->getCollection()->map(fn (Candidate $candidate) => [
                'name' => $candidate->name,
                'position' => $candidate->position->name ?? 'Aspirant',
                'image_url' => $this->candidateImageUrl($candidate->profile_picture),
                'url' => route('aspirants.show', $candidate),
            ])->values(),
            'next_page_url' => $aspirants->nextPageUrl(),
        ]);
    }

    private function candidateImageUrl(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset(Storage::url(ltrim($path, '/')));
    }
}

