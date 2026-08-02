<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PoliticalParty\ReviewPartyRequest;
use App\Http\Requests\PoliticalParty\StorePartyOfficialRequest;
use App\Http\Requests\PoliticalParty\UpdatePartyOfficialStatusRequest;
use App\Models\PoliticalParty;
use App\Models\PoliticalPartyAccountRequest;
use App\Models\PoliticalPartyCandidateClaim;
use App\Models\User;
use App\Services\Web\PoliticalPartyManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PoliticalPartyManagementController extends Controller
{
    public function __construct(
        private PoliticalPartyManagementService $management,
    ) {}

    public function index(): View
    {
        return view(
            'political-parties.management',
            $this->management->adminData(),
        );
    }

    public function storeOfficial(
        StorePartyOfficialRequest $request,
    ): RedirectResponse {
        $this->management->saveAdminOfficial(
            $request->user(),
            $request->validated(),
        );

        return back()->with('success', 'Party official access created.');
    }

    public function status(
        UpdatePartyOfficialStatusRequest $request,
        PoliticalParty $politicalParty,
        User $user,
    ): RedirectResponse {
        $this->management->updateOfficialStatus(
            $politicalParty,
            $user,
            $request->validated('status'),
        );

        return back()->with('success', 'Party official status updated.');
    }

    public function account(
        ReviewPartyRequest $request,
        PoliticalPartyAccountRequest $accountRequest,
    ): RedirectResponse {
        $this->management->reviewAccountRequest(
            $accountRequest,
            $request->validated('status'),
            $request->validated('review_notes'),
            $request->user(),
        );

        return back()->with('success', 'Party account request reviewed.');
    }

    public function claim(
        ReviewPartyRequest $request,
        PoliticalPartyCandidateClaim $claim,
    ): RedirectResponse {
        $this->management->reviewCandidateClaim(
            $claim,
            $request->validated('status'),
            $request->validated('review_notes'),
            $request->user(),
        );

        return back()->with('success', 'Party aspirant claim reviewed.');
    }

    public function document(
        PoliticalPartyAccountRequest $accountRequest,
    ): StreamedResponse {
        $path = $this->management->authorizationDocumentPath($accountRequest);

        return Storage::download($path);
    }
}
