<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CandidateFilterRequest;
use App\Http\Requests\Admin\CandidateSearchRequest;
use App\Http\Requests\Admin\CandidateStoreRequest;
use App\Http\Requests\Admin\CandidateUpdateRequest;
use App\Http\Requests\Admin\ImportCandidatesRequest;
use App\Http\Requests\Admin\UpdateCandidateApprovalRequest;
use App\Http\Requests\Admin\UpdateCandidateFeaturedRequest;
use App\Http\Requests\Web\PublicAspirantFilterRequest;
use App\Models\Candidate;
use App\Models\CandidateTransferRun;
use App\Services\Admin\CandidateClaimLinkService;
use App\Services\Admin\CandidateService;
use App\Services\Admin\CandidateTransferService;
use App\Services\Web\CandidateSeoService;
use App\Services\Web\PublicCountyService;
use App\Services\Web\UserLinkService;
use App\Services\Web\ViewTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidateController extends Controller
{
    public function __construct(
        private CandidateService $candidateService,
        private CandidateTransferService $candidateTransferService,
        private CandidateClaimLinkService $claimLinkService,
        private CandidateSeoService $candidateSeo,
        private UserLinkService $userLinkService,
        private PublicCountyService $publicCountyService,
        private ViewTracker $viewTracker,
    ) {}

    public function index(CandidateFilterRequest $request): View
    {
        return view('candidates.index', [
            ...$this->candidateService->getFormData(),
            'candidates' => $this->candidateService->getPaginatedCandidates(15, $request->filters()),
            'transferRuns' => $this->candidateTransferService->recentRuns(),
        ]);
    }

    public function search(CandidateSearchRequest $request): JsonResponse
    {
        return response()->json([
            'results' => $this->candidateService->searchOptions($request->term()),
        ]);
    }

    public function create(): View
    {
        return view('candidates.create', $this->candidateService->getFormData());
    }

    public function store(CandidateStoreRequest $request): RedirectResponse
    {
        $this->candidateService->createCandidate(
            $this->candidateService->withPermittedSupportContacts($request->validated(), null, $request->user()),
            $request->file('profile_picture'),
            $request->file('cover_photo'),
            $request->file('campaign_poster'),
            null,
            $request->file('campaign_skiza_audio')
        );

        return redirect()->route('candidates.index')
            ->with('success', 'Aspirant added successfully.');
    }

    public function edit(Candidate $candidate): View
    {
        return view('candidates.edit', $this->candidateService->getEditData($candidate));
    }

    public function update(CandidateUpdateRequest $request, Candidate $candidate): RedirectResponse
    {
        $this->candidateService->updateCandidate(
            $candidate,
            $this->candidateService->withPermittedSupportContacts($request->validated(), $candidate, $request->user()),
            $request->file('profile_picture'),
            $request->file('cover_photo'),
            $request->file('campaign_poster'),
            null,
            $request->file('campaign_skiza_audio')
        );

        return redirect(route('candidates.edit', $candidate).'#'.$request->activeTab())
            ->with('success', 'Aspirant updated successfully.');
    }

    public function toggleFeatured(UpdateCandidateFeaturedRequest $request, Candidate $candidate): JsonResponse
    {
        $this->candidateService->setFeatured($candidate, $request->boolean('featured'));

        return response()->json([
            'success' => true,
            'featured' => $candidate->featured,
        ]);
    }

    public function updateApproval(UpdateCandidateApprovalRequest $request, Candidate $candidate): RedirectResponse
    {
        $status = $request->validated('status');
        $this->candidateService->updateApprovalStatus($candidate, $status);

        return back()->with('success', 'Aspirant '.$status.' successfully.');
    }

    public function sendClaimLink(Candidate $candidate): RedirectResponse
    {
        $warning = $this->claimLinkService->send($candidate);

        if ($warning !== null) {
            return back()->with('warning', $warning);
        }

        return back()->with('success', 'Claim link queued for '.$candidate->email.'.');
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

    public function publicIndex(PublicAspirantFilterRequest $request): View
    {
        $data = $this->candidateService->getPublicIndex($request->validated(), 30);
        $data['nationalLinks'] = $this->userLinkService->forNational();

        $this->candidateSeo->applyForIndex();

        return view('aspirants.public.index', $data);
    }

    public function publicShow(Request $request, Candidate $candidate): RedirectResponse|View
    {
        abort_unless($this->candidateService->isPubliclyVisible($candidate), 404);

        if ($this->candidateService->hasStaleSlug($candidate, $request->segment(2))) {
            return redirect()->route('aspirants.show', $candidate, 301);
        }

        $candidate = $this->candidateService->getPublicShow($candidate);

        $this->viewTracker->track($candidate, $request);
        $this->candidateSeo->applyForShow($candidate);

        return view('aspirants.public.show', [
            'candidate' => $candidate,
            'candidateLinks' => $this->userLinkService->forCandidate($candidate),
            'candidateCounty' => $this->publicCountyService->countyFor($candidate),
        ]);
    }

    public function importTemplate(): StreamedResponse
    {
        return $this->candidateTransferService->importTemplateResponse();
    }

    public function import(ImportCandidatesRequest $request): RedirectResponse
    {
        $this->candidateTransferService->queueImport($request->file('file'), $request->user()->getKey());

        return redirect()->route('candidates.index')
            ->with('success', 'The aspirant CSV import has been queued. Results will appear in the Import / Export panel below.');
    }

    public function export(CandidateFilterRequest $request): RedirectResponse
    {
        $this->candidateTransferService->queueExport($request->filters(), $request->user()->getKey());

        return back()->with('success', 'The aspirant export has been queued. The download link will appear in the Import / Export panel below.');
    }

    public function exportDownload(CandidateTransferRun $run): StreamedResponse|BinaryFileResponse
    {
        return $this->candidateTransferService->downloadResponse($run);
    }

    public function transferStatus(CandidateTransferRun $run): JsonResponse
    {
        return response()->json($this->candidateTransferService->statusPayload($run));
    }

    public function publishImport(Candidate $candidate): RedirectResponse
    {
        return $this->reviewImport(
            fn () => $this->candidateService->publishImportedCandidate($candidate),
            $candidate->name.' published.'
        );
    }

    public function discardImport(Candidate $candidate): RedirectResponse
    {
        return $this->reviewImport(
            fn () => $this->candidateService->discardImportedCandidate($candidate),
            $candidate->name.' discarded.'
        );
    }

    /**
     * Flash the first validation message when an import review action is refused.
     */
    private function reviewImport(callable $action, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (ValidationException $e) {
            return back()->with('warning', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', $success);
    }
}
