<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreCandidateClaimRequest;
use App\Models\Candidate;
use App\Services\Web\CandidateClaimRequestService;
use Illuminate\Http\RedirectResponse;

class CandidateClaimRequestController extends Controller
{
    public function __construct(private CandidateClaimRequestService $claimRequestService) {}

    public function store(StoreCandidateClaimRequest $request, Candidate $candidate): RedirectResponse
    {
        $this->claimRequestService->createPublicRequest($candidate, $request->validated());

        return back()->with('success', 'Your claim request has been submitted for admin verification.');
    }
}
