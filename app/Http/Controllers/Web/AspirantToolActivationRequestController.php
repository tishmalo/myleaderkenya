<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\AspirantWorkspaceService;
use App\Http\Requests\Web\StoreAspirantToolActivationRequest;
use Illuminate\Http\RedirectResponse;

class AspirantToolActivationRequestController extends Controller
{
    public function __construct(private AspirantWorkspaceService $workspaceService) {}

    public function store(StoreAspirantToolActivationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $candidate = $this->workspaceService->candidateForUser($user);

        if (! $candidate) {
            return redirect('/aspirant/dashboard')
                ->with('warning', 'Link an aspirant profile before requesting tool activation.');
        }

        $this->workspaceService->requestToolActivation($user, $candidate, $request->validated());

        return redirect(route('aspirant.dashboard') . '#campaign-tools')
            ->with('success', 'Activation request sent to admin.');
    }
}

