<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewCandidateClaimRequest;
use App\Http\Requests\Admin\UpdateCandidateRelationshipAccessRequest;
use App\Models\CandidateClaimRequest;
use App\Services\Web\CandidateClaimRequestService;
use Illuminate\Http\RedirectResponse;

class CandidateClaimRequestController extends Controller
{
    public function __construct(private CandidateClaimRequestService $claimRequestService) {}

    public function update(ReviewCandidateClaimRequest $request, CandidateClaimRequest $claimRequest): RedirectResponse
    {
        $validated = $request->validated();

        if ($validated['status'] === CandidateClaimRequest::STATUS_APPROVED) {
            $this->claimRequestService->approve($claimRequest, $request->user(), $validated['review_note'] ?? null);

            return back()->with('success', $claimRequest->relationship === 'adopter'
                ? 'Adoption sponsorship approved without dashboard access.'
                : 'Claim request approved and linked to the aspirant.');
        }

        $this->claimRequestService->reject($claimRequest, $request->user(), $validated['review_note'] ?? null);

        return back()->with('success', 'Claim request rejected.');
    }

    public function updateDashboardAccess(UpdateCandidateRelationshipAccessRequest $request, CandidateClaimRequest $claimRequest): RedirectResponse
    {
        $enabled = (bool) $request->validated('dashboard_access_enabled');

        $this->claimRequestService->updateDashboardAccess($claimRequest, $enabled);

        return back()->with('success', $enabled ? 'Dashboard access enabled.' : 'Dashboard access disabled.');
    }
}
