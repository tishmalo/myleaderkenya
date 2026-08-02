<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PoliticalParty\StorePartyAccessRequest;
use App\Models\PoliticalParty;
use App\Services\Web\PoliticalPartyManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PoliticalPartyAccountRequestController extends Controller
{
    public function __construct(
        private PoliticalPartyManagementService $management,
    ) {}

    public function create(PoliticalParty $politicalParty): View
    {
        return view('political-parties.account-request', compact('politicalParty'));
    }

    public function store(
        StorePartyAccessRequest $request,
        PoliticalParty $politicalParty,
    ): RedirectResponse {
        $this->management->requestAccess(
            $politicalParty,
            $request->validated(),
            $request->file('authorization_document'),
        );

        return redirect()
            ->route('parties.show', $politicalParty)
            ->with('success', 'Party dashboard access request submitted for admin review.');
    }
}
