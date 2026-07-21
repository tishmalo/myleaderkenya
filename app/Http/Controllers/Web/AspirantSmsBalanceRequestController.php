<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SmsBalanceRequestStoreRequest;
use App\Services\Web\AspirantWorkspaceService;
use App\Services\Web\SmsBalanceRequestService;
use Illuminate\Http\RedirectResponse;

class AspirantSmsBalanceRequestController extends Controller
{
    public function __construct(
        private AspirantWorkspaceService $workspaceService,
        private SmsBalanceRequestService $balanceRequests
    ) {}

    public function store(SmsBalanceRequestStoreRequest $request): RedirectResponse
    {
        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect('/aspirant/dashboard')->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $this->balanceRequests->createForAspirant($candidate, $request->user(), $request->validated());

        return redirect()->route('aspirant.tools.show', 'bulk-sms')
            ->with('success', 'SMS balance support request sent to admin for follow-up.');
    }
}
