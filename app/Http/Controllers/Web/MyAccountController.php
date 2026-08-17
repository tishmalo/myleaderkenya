<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SelectAspirantWorkspaceRequest;
use App\Http\Requests\Web\ViewMyAccountRequest;
use App\Services\Web\MyAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MyAccountController extends Controller
{
    public function __construct(private MyAccountService $accounts) {}

    public function index(ViewMyAccountRequest $request): View|RedirectResponse
    {
        if ($this->accounts->shouldOpenDirectAspirantDashboard(
            $request->user(),
            $request->explicitlyRequestsAccount(),
        )) {
            return redirect()->route('aspirant.dashboard');
        }

        return view('account.index', $this->accounts->dashboardData($request->user()));
    }

    public function select(SelectAspirantWorkspaceRequest $request): RedirectResponse
    {
        $this->accounts->selectCandidate($request->user(), (int) $request->validated('candidate_id'));
        return redirect()->route('aspirant.dashboard');
    }
}
