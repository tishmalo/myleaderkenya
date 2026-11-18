<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\UpdateCandidateCampaignPrioritiesRequest;
use App\Services\Web\AspirantWorkspaceService;
use App\Services\Web\CandidateCampaignPriorityService;
use Illuminate\Http\RedirectResponse;

class CandidateCampaignPriorityController extends Controller
{
    public function __construct(
        private AspirantWorkspaceService $workspaceService,
        private CandidateCampaignPriorityService $priorities,
    ) {}

    public function update(UpdateCandidateCampaignPrioritiesRequest $request): RedirectResponse
    {
        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect(route('aspirant.dashboard') . '#campaign-priorities')
                ->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $this->priorities->save($candidate, $request->priorities(), $request->user());

        return redirect(route('aspirant.dashboard') . '#campaign-priorities')
            ->with('success', 'Campaign priorities saved and sent for admin review.');
    }
}
