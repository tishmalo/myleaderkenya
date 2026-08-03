<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SelectAspirantWorkspaceRequest;
use App\Services\Web\MyAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyAccountController extends Controller
{
    public function __construct(private MyAccountService $accounts) {}

    public function index(Request $request): View|RedirectResponse
    {
        if ($this->accounts->selectDirectAspirantCandidate($request->user())) {
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
