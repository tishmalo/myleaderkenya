<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AspirantSubmissionRequest;
use App\Http\Requests\Api\AspirantUpdateRequest;
use App\Models\Candidate;
use App\Models\NewsArticle;
use App\Services\Admin\CandidateService;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class AspirantController extends Controller
{
    public function __construct(
        private CandidateService $candidateService,
        private AspirantWorkspaceService $workspaceService
    ) {}

    public function list(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 12), 50);

        $aspirants = Candidate::with(['position', 'politicalParty'])
            ->when(Schema::hasColumn('candidates', 'approval_status'), fn ($query) => $query->where('approval_status', 'approved'))
            ->when($request->query('featured') !== null, function ($query) use ($request) {
                $query->where('featured', filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN));
            })
            ->when($request->query('county'), fn ($query, $county) => $query->where('county', $county))
            ->when($request->query('constituency'), fn ($query, $constituency) => $query->where('constituency', $constituency))
            ->when($request->query('ward'), fn ($query, $ward) => $query->where('ward', $ward))
            ->when($request->query('position', 'president'), function ($query, $position) {
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
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Candidate $candidate) => $this->formatAspirant($candidate));

        return response()->json($aspirants);
    }


    public function store(AspirantSubmissionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $phone1 = $validated['phone_1'] ?? $validated['phone'] ?? null;
        $email1 = $validated['email_1'] ?? $validated['email'] ?? null;

        $candidate = $this->candidateService->createCandidate(
            [
                'name' => $validated['name'],
                'nick_name' => $validated['nick_name'] ?? null,
                'phone' => $phone1,
                'phone_1' => $phone1,
                'phone_2' => $validated['phone_2'] ?? null,
                'email' => $email1,
                'email_1' => $email1,
                'email_2' => $validated['email_2'] ?? null,
                'position_id' => $validated['position_id'],
                'political_party_id' => $validated['political_party_id'] ?? $validated['party'] ?? null,
                'about' => $validated['about'] ?? null,
                'county' => $validated['county'] ?? null,
                'constituency' => $validated['constituency'] ?? null,
                'ward' => $validated['ward'] ?? null,
                'approval_status' => 'pending',
            ],
            $request->file('profile_picture') ?? $request->file('profile_pic'),
            $request->file('cover_photo'),
            $request->file('campaign_poster'),
            $request->file('campaign_video'),
            $request->file('campaign_skiza_audio')
        );

        $candidate->load(['position', 'politicalParty']);

        return response()->json([
            'message' => 'Aspirant registration submitted successfully. An admin will review it before it appears publicly.',
            'data' => $this->formatAspirant($candidate, true),
        ], 201);
    }

    public function update(AspirantUpdateRequest $request, Candidate $candidate): JsonResponse
    {
        if (Schema::hasColumn('candidates', 'approval_status') && $candidate->approval_status === 'approved') {
            return response()->json([
                'message' => 'Approved aspirants cannot be updated through the public API. Ask an admin to make changes.',
            ], 403);
        }

        $validated = $request->validated();
        $candidateData = $this->candidateUpdateData($validated);

        if (Schema::hasColumn('candidates', 'approval_status')) {
            $candidateData['approval_status'] = 'pending';
        }

        $this->candidateService->updateCandidate(
            $candidate,
            $candidateData,
            $request->file('profile_picture') ?? $request->file('profile_pic'),
            $request->file('cover_photo'),
            $request->file('campaign_poster'),
            $request->file('campaign_video'),
            $request->file('campaign_skiza_audio')
        );

        $candidate->refresh()->load(['position', 'politicalParty']);

        return response()->json([
            'message' => 'Aspirant submission updated successfully. An admin will review it before it appears publicly.',
            'data' => $this->formatAspirant($candidate, true),
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return response()->json([
                'message' => 'No aspirant profile is linked to this account yet.',
                'data' => null,
            ], 404);
        }

        $candidate->loadMissing(['position', 'politicalParty']);

        return response()->json([
            'data' => $this->formatAspirant($candidate, true),
        ]);
    }
    public function show(Candidate $candidate): JsonResponse
    {
        if (Schema::hasColumn('candidates', 'approval_status') && $candidate->approval_status !== 'approved') {
            abort(404);
        }

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

    private function candidateUpdateData(array $validated): array
    {
        $data = [];

        foreach ([
            'name',
            'nick_name',
            'phone_2',
            'email_2',
            'position_id',
            'about',
            'county',
            'constituency',
            'ward',
        ] as $field) {
            if (array_key_exists($field, $validated)) {
                $data[$field] = $validated[$field];
            }
        }

        if (array_key_exists('phone_1', $validated) || array_key_exists('phone', $validated)) {
            $phone1 = $validated['phone_1'] ?? $validated['phone'] ?? null;
            $data['phone'] = $phone1;
            $data['phone_1'] = $phone1;
        }

        if (array_key_exists('email_1', $validated) || array_key_exists('email', $validated)) {
            $email1 = $validated['email_1'] ?? $validated['email'] ?? null;
            $data['email'] = $email1;
            $data['email_1'] = $email1;
        }

        if (array_key_exists('political_party_id', $validated) || array_key_exists('party', $validated)) {
            $data['political_party_id'] = $validated['political_party_id'] ?? $validated['party'] ?? null;
        }

        return $data;
    }
    private function formatAspirant(Candidate $candidate, bool $includeAbout = false): array
    {
        $data = [
            'id' => $candidate->id,
            'name' => $candidate->name,
            'nick_name' => $candidate->nick_name,
            'position_id' => $candidate->position_id,
            'political_party_id' => $candidate->political_party_id,
            'party' => $candidate->political_party_id,
            'phone' => $candidate->maskedPhone(),
            'email' => $candidate->maskedEmail(),
            'featured' => (bool) $candidate->featured,
            'profile_picture' => $candidate->profile_picture,
            'profile_picture_url' => $this->storageUrl($candidate->profile_picture),
            'cover_photo' => $candidate->cover_photo,
            'cover_photo_url' => $this->storageUrl($candidate->cover_photo),
            'campaign_poster' => $candidate->campaign_poster,
            'campaign_poster_url' => $this->storageUrl($candidate->campaign_poster),
            'campaign_video' => $candidate->campaign_video,
            'campaign_video_url' => $this->storageUrl($candidate->campaign_video),
            'campaign_skiza_audio' => $candidate->campaign_skiza_audio,
            'campaign_skiza_audio_url' => $this->storageUrl($candidate->campaign_skiza_audio),
            'phone_1' => $candidate->phone_1,
            'phone_2' => $candidate->phone_2,
            'email_1' => $candidate->email_1,
            'email_2' => $candidate->email_2,
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



