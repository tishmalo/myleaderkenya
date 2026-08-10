<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\Api\UserRepositoryInterface as AccountRepositoryInterface;
use App\Contracts\Repositories\Admin\CampaignToolRepositoryInterface;
use App\Http\Requests\Web\AspirantEmailAvailabilityRequest;
use App\Http\Requests\Web\AspirantRegisterRequest;
use App\Models\Candidate;
use App\Models\PoliticalParty;
use App\Models\Position;
use App\Models\User;
use App\Services\Admin\CandidateService;
use App\Services\Web\CandidateClaimRequestService;
use App\Services\Web\AspirantAdoptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AspirantRegistrationController extends Controller
{
    public function __construct(
        private CandidateService $candidateService,
        private CandidateClaimRequestService $claimRequestService,
        private AspirantAdoptionService $adoptionService,
        private CampaignToolRepositoryInterface $campaignTools,
        private AccountRepositoryInterface $accounts
    ) {}

    public function create(Request $request): View
    {
        $requestedCandidateId = old('candidate_id', $request->query('candidate_id'));
        $selectedCandidate = null;

        if ($requestedCandidateId !== null && $requestedCandidateId !== '') {
            abort_unless(filter_var($requestedCandidateId, FILTER_VALIDATE_INT) !== false, 404);
            $candidate = $this->publicCandidateQuery()->findOrFail((int) $requestedCandidateId);
            $selectedCandidate = $this->publicCandidateData($candidate);
        }

        return view('aspirants.register', [
            'positions' => Position::ordered()->get(),
            'politicalParties' => PoliticalParty::published()->ordered()->get(),
            'selectedCandidate' => $selectedCandidate,
            'adoptableTools' => $this->campaignTools->publishedForSponsorship(),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $term = Str::limit(trim((string) $request->query('q', '')), 80, '');

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []])
                ->header('Cache-Control', 'no-store, private');
        }


        $candidates = $this->publicCandidateQuery()

            ->where(function ($query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('nick_name', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->limit(12)
            ->get()

            ->map(fn (Candidate $candidate): array => $this->publicCandidateData($candidate));


        return response()->json(['results' => $candidates])
            ->header('Cache-Control', 'no-store, private')
            ->header('Pragma', 'no-cache');
    }

    public function emailAvailability(AspirantEmailAvailabilityRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $exists = $this->accounts->existsByEmailHash($validated['email']);

        return response()->json([
            'available' => ! $exists,
            'message' => $exists
                ? 'An account already exists for this email.'
                : 'Email is available.',
        ])->header('Cache-Control', 'no-store, private')
            ->header('Pragma', 'no-cache');
    }
    public function store(AspirantRegisterRequest $request): RedirectResponse|View
    {
        $validated = $request->validated();
        $authenticatedUser = $request->user();
        $isRepresentative = $validated['submission_mode'] === 'representative';
        $isAdoption = $validated['submission_mode'] === 'adoption';
        $needsClaim = $isRepresentative || $isAdoption;
        $relationship = $isAdoption
            ? 'adopter'
            : ($isRepresentative ? $validated['relationship'] : 'aspirant');

        if (! empty($validated['candidate_id'])) {
            $candidate = Candidate::query()
                ->select(['id', 'name'])
                ->when(
                    Schema::hasColumn('candidates', 'approval_status'),
                    fn ($query) => $query->where('approval_status', 'approved')
                )
                ->findOrFail($validated['candidate_id']);

            DB::transaction(function () use ($candidate, $authenticatedUser, $validated, $relationship, $isRepresentative, $isAdoption): void {
                $claim = $authenticatedUser
                    ? $this->claimRequestService->createAuthenticatedRequest($candidate, $authenticatedUser, $relationship)
                    : $this->claimRequestService->createPublicRequest($candidate, [
                        'relationship' => $relationship,
                        'name' => $validated['account_name'] ?? $candidate->name,
                        'email' => $validated['account_email'],
                        'phone' => $validated['account_phone'] ?? null,
                        'password' => $validated['password'],
                        '_email_field' => 'account_email',
                    ]);

                if ($isAdoption) {
                    $this->adoptionService->create(
                        $candidate,
                        $authenticatedUser ?: $claim->user()->firstOrFail(),
                        $validated['adoption_tool_ids']
                    );
                }
            });

            $message = $isAdoption
                ? 'Your aspirant adoption and selected sponsorship tools have been submitted for admin verification.'
                : 'Your access request has been submitted for admin verification.';

            if ($isAdoption) {
                return $this->adoptionRedirect($request, $message, $authenticatedUser !== null);
            }

            return $this->registrationRedirect($request, $message);
        }

        DB::transaction(function () use ($request, $validated, $authenticatedUser, $needsClaim, $relationship, $isAdoption): void {
            $candidateData = [
                'name' => $validated['aspirant_name'],
                'nick_name' => $validated['nick_name'] ?? null,
                'email' => $validated['aspirant_email'] ?? null,
                'phone' => $validated['aspirant_phone'] ?? null,
                'position_id' => $validated['position_id'],
                'political_party_id' => $validated['political_party_id'] ?? null,
                'about' => $validated['about'] ?? null,
                'county' => $validated['county'] ?? null,
                'constituency' => $validated['constituency'] ?? null,
                'ward' => $validated['ward'] ?? null,
                'approval_status' => 'pending',
            ];

            if (! $needsClaim && ! $authenticatedUser) {
                $user = User::create([
                    'name' => $validated['aspirant_name'],
                    'username' => $this->uniqueUsername($validated['aspirant_name']),
                    'email' => $validated['aspirant_email'],
                    'password' => $validated['password'],
                    'role' => 'user',
                    'phone' => $validated['aspirant_phone'] ?? null,
                    'relationship' => 'aspirant',
                    'is_aspirant' => true,
                ]);
                $candidateData['user_id'] = $user->id;
            } elseif (! $needsClaim) {
                $candidateData['user_id'] = $authenticatedUser->id;
            }

            $candidate = $this->candidateService->createCandidate(
                $candidateData,
                $request->file('profile_picture'),
                $request->file('cover_photo')
            );

            if ($needsClaim) {
                $claim = $authenticatedUser
                    ? $this->claimRequestService->createAuthenticatedRequest($candidate, $authenticatedUser, $relationship)
                    : $this->claimRequestService->createPublicRequest($candidate, [
                        'relationship' => $relationship,
                        'name' => $validated['account_name'],
                        'email' => $validated['account_email'],
                        'phone' => $validated['account_phone'] ?? null,
                        'password' => $validated['password'],
                        '_email_field' => 'account_email',
                    ]);

                if ($isAdoption) {
                    $this->adoptionService->create(
                        $candidate,
                        $authenticatedUser ?: $claim->user()->firstOrFail(),
                        $validated['adoption_tool_ids']
                    );
                }
            }
        });

        $message = $isAdoption
            ? 'The aspirant profile, adoption request, and selected sponsorship tools have been submitted for admin verification.'
            : ($isRepresentative
                ? 'The aspirant profile and your access request have been submitted for admin verification.'
                : 'Your aspirant registration has been submitted. Sign in while an admin reviews your profile.');

        if ($isAdoption) {
            return $this->adoptionRedirect($request, $message, $authenticatedUser !== null);
        }

        return $this->registrationRedirect($request, $message, ! $authenticatedUser && ! $needsClaim);
    }

    private function adoptionRedirect(Request $request, string $message, bool $authenticated): RedirectResponse|View
    {
        $successMessage = $message.($authenticated
            ? ' Open My Toolbox whenever you are ready to choose and fund packages.'
            : ' Sign in later to choose and fund packages from My Toolbox.');

        if ($request->boolean('modal')) {
            $request->session()->flash('success', $successMessage);

            return view('aspirants.modal-submitted', ['redirectUrl' => route('landing')]);
        }

        return redirect()->route('landing')->with('success', $successMessage);
    }

    private function registrationRedirect(Request $request, string $message, bool $toLogin = false): RedirectResponse
    {
        if ($toLogin) {
            return redirect($request->boolean('modal') ? route('login', ['modal' => 1]) : route('login'))
                ->with('status', $message);
        }

        $parameters = array_filter([
            'modal' => $request->boolean('modal') ? 1 : null,
            'candidate_id' => $request->input('candidate_id'),
        ]);

        return redirect()->route('aspirants.register', $parameters)
            ->with('success', $message);
    }

    private function publicCandidateQuery()
    {
        return Candidate::query()
            ->select([
                'id', 'name', 'nick_name', 'profile_picture',
                'position_id', 'political_party_id', 'country', 'county',
                'constituency', 'ward',
            ])
            ->with(['position:id,name', 'politicalParty:id,name'])
            ->when(
                Schema::hasColumn('candidates', 'approval_status'),
                fn ($query) => $query->where('approval_status', 'approved')
            );
    }

    private function publicCandidateData(Candidate $candidate): array
    {
        return [
            'id' => $candidate->id,
            'name' => $candidate->name,
            'nickname' => $candidate->nick_name,
            'image_url' => $candidate->profile_picture ? Storage::url($candidate->profile_picture) : null,
            'position' => $candidate->position?->name,
            'party' => $candidate->politicalParty?->name,
            'jurisdiction' => collect([$candidate->ward, $candidate->constituency, $candidate->county, $candidate->country])
                ->filter()->unique()->implode(', '),
        ];
    }

    private function uniqueUsername(string $name): string
    {
        $base = Str::limit(Str::slug($name, '_'), 40, '') ?: 'aspirant';
        $username = $base;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . '_' . $suffix++;
        }

        return $username;
    }
}
