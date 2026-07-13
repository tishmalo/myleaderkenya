<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CampaignTool;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AspirantDashboardController extends Controller
{
    public function __construct(private AspirantWorkspaceService $workspaceService) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $candidate = $this->workspaceService->candidateForUser($user);
        $campaignTools = CampaignTool::published()->ordered()->get();
        $scope = $this->workspaceService->scopeForCandidate($candidate);

        return view('aspirants.dashboard', [
            'user' => $user,
            'candidate' => $candidate,
            'campaignTools' => $campaignTools,
            'toolModules' => $this->workspaceService->toolModules($campaignTools, $candidate),
            'voterScope' => $scope,
        ]);
    }
}
