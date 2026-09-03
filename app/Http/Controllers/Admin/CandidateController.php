<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CandidateStoreRequest;
use App\Http\Requests\Admin\CandidateUpdateRequest;
use App\Http\Requests\Admin\UpdateCandidateApprovalRequest;
use App\Http\Requests\Web\PublicAspirantFilterRequest;
use App\Jobs\ExportCandidates;
use App\Jobs\ImportCandidates;
use App\Models\Candidate;
use App\Models\CandidateTransferRun;
use App\Notifications\CandidateClaimLinkNotification;
use App\Services\Admin\CandidateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidateController extends Controller
{
    public function __construct(
        private CandidateService $candidateService
    ) {}

    public function index()
    {
        $filters = request()->only(['candidate', 'position', 'political_party', 'approval_status', 'account_claim', 'import_filter']);
        $candidates = $this->candidateService->getPaginatedCandidates(15, $filters);
        $formData = $this->candidateService->getFormData();
        $transferRuns = CandidateTransferRun::latest()->limit(10)->get();

        return view('candidates.index', array_merge($formData, compact('candidates', 'transferRuns')));
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
                'name' => $candidate->name,
                'nickname' => $candidate->nick_name,
                'text' => trim($candidate->name . ($candidate->nick_name ? ' (' . $candidate->nick_name . ')' : '')),
                'image_url' => null,
                'position' => null,
                'party' => null,
                'jurisdiction' => null,
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
            $request->file('cover_photo'),
            $request->file('campaign_poster'),
            null,
            $request->file('campaign_skiza_audio')
        );

        return redirect()->route('candidates.index')
                         ->with('success', 'Aspirant added successfully.');
    }

    public function edit(Candidate $candidate)
    {
        $candidate->load(['campaignPriorities.category', 'parliamentMember']);

        return view('candidates.edit', array_merge(
            $this->candidateService->getFormData(),
            $this->candidateService->getAdminDonationData($candidate),
            compact('candidate')
        ));
    }

    public function update(CandidateUpdateRequest $request, Candidate $candidate)
    {
        $this->candidateService->updateCandidate(
            $candidate,
            $this->filterSupportContactsData($request->validated(), $request, $candidate),
            $request->file('profile_picture'),
            $request->file('cover_photo'),
            $request->file('campaign_poster'),
            null,
            $request->file('campaign_skiza_audio')
        );

        $activeTab = in_array($request->input('active_tab'), [
            'profile-basic',
            'profile-political',
            'profile-social',
            'profile-media',
            'profile-support',
            'tools',
            'priorities',
            'donations',
            'parliament',
        ], true) ? $request->input('active_tab') : 'profile-basic';

        return redirect(route('candidates.edit', $candidate) . '#' . $activeTab)
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
    public function updateApproval(UpdateCandidateApprovalRequest $request, Candidate $candidate): RedirectResponse
    {
        $status = $request->validated('status');
        $this->candidateService->updateApprovalStatus($candidate, $status);

        return back()->with('success', 'Aspirant ' . $status . ' successfully.');
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
    public function destroy(Request $request, Candidate $candidate): RedirectResponse|JsonResponse
    {
        $this->candidateService->deleteCandidate($candidate);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Aspirant deleted successfully.',
            ]);
        }

        return redirect()->route('candidates.index')
            ->with('success', 'Aspirant deleted successfully.');
    }

    public function publicIndex(PublicAspirantFilterRequest $request)
    {
        $data = $this->candidateService->getPublicIndex($request->validated(), 30);

        return view('aspirants.public.index', $data);
    }

    public function publicShow(Candidate $candidate): RedirectResponse|View
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('candidates', 'approval_status') && $candidate->approval_status !== 'approved') {
            abort(404);
        }

        if ($candidate->slug && request()->segment(2) !== $candidate->slug) {
            return redirect()->route('aspirants.show', $candidate, 301);
        }

        $candidate = $this->candidateService->getPublicShow($candidate);
        return view('aspirants.public.show', compact('candidate'));
    }

    public function importTemplate(): StreamedResponse
    {
        $headers = [
            'name', 'nick_name', 'phone', 'email', 'political_party',
            'position', 'county', 'constituency', 'ward', 'about',
        ];

        return response()->streamDownload(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $headers);
            fputcsv($out, [
                'Jane Doe', 'JD', '0712345678', 'jane@example.com', 'UDA',
                'Governor', 'Nairobi', '', '', 'About text',
            ]);
            fclose($out);
        }, 'candidates_import_template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $path = $request->file('file')->store('imports');

        $run = CandidateTransferRun::create([
            'type' => 'import',
            'status' => 'pending',
            'source_path' => $path,
            'requested_by' => $request->user()->id,
        ]);

        ImportCandidates::dispatch($run->id);

        return redirect()->route('candidates.index')
            ->with('success', 'The aspirant CSV import has been queued. Results will appear in the Import / Export panel below.');
    }

    public function export(Request $request): RedirectResponse
    {
        $filters = $request->only(['candidate', 'position', 'political_party', 'approval_status', 'account_claim', 'import_filter']);

        $run = CandidateTransferRun::create([
            'type' => 'export',
            'status' => 'pending',
            'filters' => $filters,
            'requested_by' => $request->user()->id,
        ]);

        ExportCandidates::dispatch($run->id);

        return back()->with('success', 'The aspirant export has been queued. The download link will appear in the Import / Export panel below.');
    }

    public function exportDownload(CandidateTransferRun $run): BinaryFileResponse
    {
        abort_unless($run->type === 'export' && $run->status === 'complete' && $run->result_path, 404);

        return Storage::disk('local')->download($run->result_path, $run->download_name ?? 'candidates.csv');
    }

    public function transferStatus(CandidateTransferRun $run): JsonResponse
    {
        return response()->json([
            'id' => $run->id,
            'type' => $run->type,
            'status' => $run->status,
            'imported_count' => $run->imported_count,
            'linked_count' => $run->linked_count,
            'skipped_count' => $run->skipped_count,
            'exported_count' => $run->exported_count,
            'error_message' => $run->error_message,
            'errors' => $run->errors,
            'download_url' => ($run->type === 'export' && $run->status === 'complete' && $run->result_path)
                ? route('candidates.export.download', $run)
                : null,
        ]);
    }

    public function publishImport(Candidate $candidate): RedirectResponse
    {
        try {
            $this->candidateService->publishImportedCandidate($candidate);
        } catch (ValidationException $e) {
            return back()->with('warning', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', $candidate->name.' published.');
    }

    public function discardImport(Candidate $candidate): RedirectResponse
    {
        try {
            $this->candidateService->discardImportedCandidate($candidate);
        } catch (ValidationException $e) {
            return back()->with('warning', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', $candidate->name.' discarded.');
    }
}

