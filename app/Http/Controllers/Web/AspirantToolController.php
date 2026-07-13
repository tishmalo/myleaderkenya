<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AspirantToolController extends Controller
{
    public function __construct(private AspirantWorkspaceService $workspaceService) {}

    public function show(Request $request, string $key): View|RedirectResponse
    {
        $definitions = $this->workspaceService->toolDefinitions();

        if (! isset($definitions[$key])) {
            abort(404);
        }

        $tool = $this->workspaceService->publishedToolForKey($key);

        if (! $tool) {
            return redirect('/aspirant/dashboard')
                ->with('warning', 'That campaign tool is not enabled yet. Ask an admin to publish it first.');
        }

        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect('/aspirant/dashboard')
                ->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $module = array_merge($definitions[$key], [
            'key' => $key,
            'tool' => $tool,
        ]);
        $scope = $this->workspaceService->scopeForCandidate($candidate);
        $isBlocked = $module['voter_facing'] && $scope['missing'];
        $voterQuery = $this->workspaceService->registeredVotersQuery($scope);
        $voterCount = $module['voter_facing'] && ! $isBlocked ? (clone $voterQuery)->count() : null;
        $recentVoters = $module['voter_facing'] && ! $isBlocked
            ? (clone $voterQuery)
                ->select('name', 'username', 'phone', 'county', 'constituency', 'ward', 'polling_station', 'created_at')
                ->latest()
                ->take(8)
                ->get()
            : collect();

        return view('aspirants.tools.show', [
            'candidate' => $candidate,
            'module' => $module,
            'scope' => $scope,
            'isBlocked' => $isBlocked,
            'voterCount' => $voterCount,
            'recentVoters' => $recentVoters,
        ]);
    }
}
