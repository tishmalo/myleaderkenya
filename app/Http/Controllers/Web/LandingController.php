<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Services\Admin\SettingService;
use App\Services\SeoService;
use App\Services\Web\LandingService;
use App\Services\Web\PollService;
use App\Support\HomepageCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LandingController extends Controller
{
    public function __construct(
        protected LandingService $landingService,
        private SettingService $settingService,
        private PollService $pollService,
        private SeoService $seo
    ) {}

    public function index()
{

    $this->seo->setTitle('Kenya Aspirants & Candidates 2027 | My Leader Kenya')
        ->setDescription('Discover all 2027 Kenya presidential, governor, senator, MP, Women Rep and MCA aspirants. Register to vote (Niko Kadi), follow polls, news and campaign tools on My Leader Kenya.')
        ->setCanonical(url('/'));

    // WebSite schema
    $this->seo->addSchema([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'My Leader Kenya',
        'url' => url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => url('/aspirants?search={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ],
    ]);

    // Organization schema
    $this->seo->addSchema([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'My Leader Kenya',
        'url' => url('/'),
        'logo' => asset('images/myleader.png'),
        'sameAs' => [
            // Add your social links here if you have them
            // 'https://twitter.com/myleaderkenya',
            // 'https://facebook.com/myleaderkenya',
            'https://www.facebook.com/profile.php?id=61593473856631',
            'https://www.instagram.com/myleaderkenya/',
            'https://x.com/MyleaderKE'
        ],
    ]);

    $landingData = $this->landingService->getLandingData();
    $landingData['recaptchaSiteKey'] = $this->settingService->recaptchaSiteKey();

    $landingData['polls'] = $this->pollService->homepagePolls(request()->user());
    $landingData['needsLocationPrompt'] = $this->pollService->needsLocationPrompt(request()->user());

    return view('landing', $landingData);
}

    public function featuredAspirants(Request $request)
    {
        $perPage = min(max((int) $request->query('per_page', 20), 2), 40);

        $page = max((int) $request->query('page', 1), 1);

        $payload = Cache::remember(
            HomepageCache::key('featured-aspirants', ['per_page' => $perPage, 'page' => $page]),
            HomepageCache::ttl(),
            function () use ($perPage): array {
                $aspirants = Candidate::query()
                    ->with(['position', 'politicalParty'])
                    ->where('featured', true)
                    ->when(Schema::hasColumn('candidates', 'approval_status'), fn ($query) => $query->where('approval_status', 'approved'))
                    ->whereNotNull('profile_picture')
                    ->where('profile_picture', '!=', '')
                    ->latest('created_at')
                    ->paginate($perPage);

                return [
                    'data' => $aspirants->getCollection()->map(fn (Candidate $candidate) => [
                        'name' => $candidate->name,
                        'position' => $candidate->position->name ?? 'Aspirant',
                        'area' => $this->candidateArea($candidate),
                        'party' => $candidate->politicalParty->abbreviation
                            ?? $candidate->politicalParty->name
                            ?? null,
                        'image_url' => $this->candidateImageUrl($candidate->profile_picture),
                        'url' => route('aspirants.show', $candidate),
                    ])->values(),
                    'next_page_url' => $aspirants->nextPageUrl(),
                ];
            }
        );

        return response()->json($payload);
    }

    private function candidateArea(Candidate $candidate): ?string
    {
        return $candidate->display_area;
    }

    private function candidateImageUrl(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset(Storage::url(ltrim($path, '/')));
    }
}
