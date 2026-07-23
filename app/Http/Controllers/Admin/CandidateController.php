<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CandidateStoreRequest;
use App\Http\Requests\Admin\CandidateUpdateRequest;
use App\Models\Candidate;
use App\Notifications\CandidateClaimLinkNotification;
use App\Services\Admin\CandidateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CandidateController extends Controller
{
    public function __construct(
        private CandidateService $candidateService
    ) {}

    public function index()
    {
        $filters = request()->only(['candidate', 'position', 'political_party', 'approval_status']);
        $candidates = $this->candidateService->getPaginatedCandidates(15, $filters);
        $formData = $this->candidateService->getFormData();

        return view('candidates.index', array_merge($formData, compact('candidates')));
    }


    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $candidates = Candidate::query()
            ->select(['id', 'name', 'nick_name'])
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('nick_name', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn (Candidate $candidate) => [
                'id' => $candidate->id,
                'text' => trim($candidate->name . ($candidate->nick_name ? ' (' . $candidate->nick_name . ')' : '')),
            ]);

        return response()->json(['results' => $candidates]);
    }
    public function create()
    {
        return view('candidates.create', $this->candidateService->getFormData());
    }

    public function store(CandidateStoreRequest $request)
    {
        $this->candidateService->createCandidate(
            $this->filterSupportContactsData($request->validated(), $request, null),
            $request->file('profile_picture'),
            $request->file('cover_photo')
        );

        return redirect()->route('candidates.index')
                         ->with('success', 'Aspirant added successfully.');
    }

    public function edit(Candidate $candidate)
    {
        return view('candidates.edit', array_merge($this->candidateService->getFormData(), compact('candidate')));
    }

    public function update(CandidateUpdateRequest $request, Candidate $candidate)
    {
        $this->candidateService->updateCandidate(
            $candidate,
            $this->filterSupportContactsData($request->validated(), $request, $candidate),
            $request->file('profile_picture'),
            $request->file('cover_photo')
        );

        return redirect()->route('candidates.index')
                         ->with('success', 'Aspirant updated successfully.');
    }

    public function toggleFeatured(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'featured' => ['required', 'boolean'],
        ]);

        $candidate->update(['featured' => $data['featured']]);

        return response()->json([
            'success' => true,
            'featured' => $candidate->featured,
        ]);
    }

    public function updateApproval(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'approval_status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $candidate->update(['approval_status' => $data['approval_status']]);

        return response()->json([
            'success' => true,
            'approval_status' => $candidate->approval_status,
        ]);
    }

    public function sendClaimLink(Candidate $candidate)
    {
        if ($candidate->user_id || $candidate->claimed_at) {
            return back()->with('warning', 'This aspirant account has already been claimed.');
        }

        if (blank($candidate->email)) {
            return back()->with('warning', 'Add an email address before sending a claim link.');
        }

        $token = Str::random(64);
        $expiresAt = now()->addDays(7);

        $candidate->forceFill([
            'claim_token_hash' => hash('sha256', $token),
            'claim_token_expires_at' => $expiresAt,
            'claim_sent_at' => now(),
        ])->save();

        $claimUrl = route('aspirants.claim.show', [$candidate, $token]);

        Notification::route('mail', $candidate->email)
            ->notify(new CandidateClaimLinkNotification($candidate->name, $claimUrl, $expiresAt));

        return back()->with('success', 'Claim link queued for ' . $candidate->email . '.');
    }
    private function filterSupportContactsData(array $data, Request $request, ?Candidate $candidate): array
    {
        if (! array_key_exists('support_contacts', $data)) {
            return $data;
        }

        $user = $request->user();
        $contacts = collect($data['support_contacts'] ?? []);

        if (! $candidate) {
            if (! $user?->canAccess('support-groups.create')) {
                unset($data['support_contacts']);
            }

            return $data;
        }

        $existingIds = $candidate->supportContacts()->pluck('id')->map(fn ($id) => (string) $id);
        $submittedIds = $contacts->pluck('id')->filter()->map(fn ($id) => (string) $id);

        $requiresCreate = $contacts->contains(fn (array $contact) => empty($contact['id']));
        $requiresUpdate = $submittedIds->intersect($existingIds)->isNotEmpty();
        $requiresDelete = $existingIds->diff($submittedIds)->isNotEmpty();

        $allowed = (! $requiresCreate || $user?->canAccess('support-groups.create'))
            && (! $requiresUpdate || $user?->canAccess('support-groups.update'))
            && (! $requiresDelete || $user?->canAccess('support-groups.delete'));

        if (! $allowed) {
            unset($data['support_contacts']);
        }

        return $data;
    }
    public function destroy(Candidate $candidate)
    {
        $this->candidateService->deleteCandidate($candidate);

        return response()->json([
            'success' => true,
            'message' => 'Aspirant deleted successfully.'
        ]);
    }

    public function publicIndex(Request $request)
    {
        if (! $request->filled('position')) {
            $request->merge(['position' => 'president']);
        }

        $data = $this->candidateService->getPublicIndex($request->only(['candidate', 'search', 'position', 'political_party', 'country', 'county', 'constituency', 'ward', 'bloc']), 4);
        return view('aspirants.public.index', $data);
    }

    public function publicShow(Candidate $candidate)
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('candidates', 'approval_status') && $candidate->approval_status !== 'approved') {
            abort(404);
        }

        $candidate = $this->candidateService->getPublicShow($candidate);
        return view('aspirants.public.show', compact('candidate'));
    }
}
