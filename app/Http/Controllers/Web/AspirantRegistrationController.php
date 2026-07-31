<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AspirantRegisterRequest;
use App\Models\Candidate;
use App\Models\PoliticalParty;
use App\Models\Position;
use App\Models\User;
use App\Services\Admin\CandidateService;
use App\Services\Web\CandidateClaimRequestService;
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
        private CandidateClaimRequestService $claimRequestService
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

    public function emailAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        $exists = User::query()
            ->where('email_hash', hash('sha256', Str::lower(trim($validated['email']))))
            ->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists
                ? 'An account already exists for this email.'
                : 'Email is available.',
        ])->header('Cache-Control', 'no-store, private')
            ->header('Pragma', 'no-cache');
    }
    public function store(AspirantRegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $isRepresentative = $validated['submission_mode'] === 'representative';
        $relationship = $isRepresentative ? $validated['relationship'] : 'aspirant';

        if (! empty($validated['candidate_id'])) {
            $candidate = Candidate::query()
                ->select(['id', 'name'])
                ->when(
                    Schema::hasColumn('candidates', 'approval_status'),
                    fn ($query) => $query->where('approval_status', 'approved')
                )
                ->findOrFail($validated['candidate_id']);

            $this->claimRequestService->createPublicRequest($candidate, [
                'relationship' => $relationship,
                'name' => $isRepresentative ? $validated['account_name'] : $candidate->name,
                'email' => $validated['account_email'],
                'phone' => $validated['account_phone'] ?? null,
                'password' => $validated['password'],
                '_email_field' => 'account_email',
            ]);

            return $this->registrationRedirect($request, 'Your access request has been submitted for admin verification.');
        }

        DB::transaction(function () use ($request, $validated, $isRepresentative, $relationship): void {
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

            if (! $isRepresentative) {
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
            }

            $candidate = $this->candidateService->createCandidate(
                $candidateData,
                $request->file('profile_picture'),
                $request->file('cover_photo')
            );

            if ($isRepresentative) {
                $this->claimRequestService->createPublicRequest($candidate, [
                    'relationship' => $relationship,
                    'name' => $validated['account_name'],
                    'email' => $validated['account_email'],
                    'phone' => $validated['account_phone'] ?? null,
                    'password' => $validated['password'],
                    '_email_field' => 'account_email',
                ]);
            }
        });

        $message = $isRepresentative
            ? 'The aspirant profile and your access request have been submitted for admin verification.'
            : 'Your aspirant registration has been submitted. Sign in while an admin reviews your profile.';

        return $this->registrationRedirect($request, $message, ! $isRepresentative);
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