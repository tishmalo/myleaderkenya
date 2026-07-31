<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PoliticalParty\DistributePartyTokensRequest;
use App\Http\Requests\PoliticalParty\PurchasePartyTokensRequest;
use App\Http\Requests\PoliticalParty\StorePartyCandidateRequest;
use App\Http\Requests\PoliticalParty\StorePartyClaimRequest;
use App\Http\Requests\PoliticalParty\StorePartyOfficialRequest;
use App\Http\Requests\PoliticalParty\UpdatePartyCandidateRequest;
use App\Models\Candidate;
use App\Models\User;
use App\Services\Web\PoliticalPartyManagementService;
use App\Services\Web\PoliticalPartyTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PoliticalPartyDashboardController extends Controller
{
    public function __construct(
        private PoliticalPartyManagementService $management,
        private PoliticalPartyTokenService $tokens,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'position', 'approval_status']);

        return view(
            'political-parties.dashboard.index',
            $this->management->dashboardData($request->user(), $filters),
        );
    }

    public function createCandidate(Request $request): View
    {
        return view(
            'political-parties.dashboard.candidate-form',
            $this->management->candidateFormData($request->user(), new Candidate),
        );
    }

    public function storeCandidate(
        StorePartyCandidateRequest $request,
    ): RedirectResponse {
        $this->management->createCandidate(
            $request->user(),
            $request->validated(),
            $request->allFiles(),
        );

        return redirect()
            ->route('party.dashboard')
            ->with('success', 'Aspirant added and submitted for admin approval.');
    }

    public function editCandidate(Request $request, Candidate $candidate): View
    {
        return view(
            'political-parties.dashboard.candidate-form',
            $this->management->candidateFormData($request->user(), $candidate),
        );
    }

    public function updateCandidate(
        UpdatePartyCandidateRequest $request,
        Candidate $candidate,
    ): RedirectResponse {
        $this->management->updateCandidate(
            $request->user(),
            $candidate,
            $request->validated(),
            $request->allFiles(),
        );

        return redirect()
            ->route('party.candidates.edit', $candidate)
            ->with('success', 'Aspirant updated successfully.');
    }

    public function claim(StorePartyClaimRequest $request): RedirectResponse
    {
        $this->management->createClaim(
            $request->user(),
            (int) $request->validated('candidate_id'),
        );

        return back()->with('success', 'Aspirant claim submitted for admin review.');
    }

    public function invite(StorePartyOfficialRequest $request): RedirectResponse
    {
        $party = $this->management->partyForUser($request->user());
        $this->management->saveOfficial(
            $request->user(),
            $party,
            $request->validated(),
        );

        return back()->with('success', 'Party official access saved.');
    }

    public function removeOfficial(Request $request, User $user): RedirectResponse
    {
        $this->management->removeOfficial($request->user(), $user);

        return back()->with('success', 'Party official removed.');
    }

    public function purchase(PurchasePartyTokensRequest $request): RedirectResponse
    {
        $checkoutUrl = $this->management->purchaseTokens(
            $request->user(),
            $request->validated(),
        );

        return redirect()->away($checkoutUrl);
    }

    public function callback(Request $request): RedirectResponse
    {
        $result = $this->tokens->callback($request->query());
        $flashType = $result['status'] === 'success' ? 'success' : 'warning';

        return redirect()
            ->route('party.dashboard')
            ->with($flashType, $result['message']);
    }

    public function distribute(
        DistributePartyTokensRequest $request,
    ): RedirectResponse {
        try {
            $this->management->distributeTokens(
                $request->user(),
                $request->validated(),
            );
        } catch (RuntimeException $exception) {
            return back()->with('warning', $exception->getMessage());
        }

        return back()->with(
            'success',
            number_format($request->integer('amount'))
                .' tokens distributed successfully.',
        );
    }
}
