<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ReplyAspirantSupportRequest;
use App\Models\AspirantSupportPayment;
use App\Services\Web\AspirantSupportService;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AspirantSupportController extends Controller
{
    public function __construct(
        private AspirantWorkspaceService $workspaces,
        private AspirantSupportService $supports
    ) {}

    public function index(Request $request): View
    {
        $candidate = $this->workspaces->candidateForUser($request->user()) ?? abort(404);
        return view('aspirants.support.index', ['candidate' => $candidate] + $this->supports->dataForCandidate($candidate->id));
    }

    public function reply(ReplyAspirantSupportRequest $request, AspirantSupportPayment $aspirantSupportPayment): RedirectResponse
    {
        $candidate = $this->workspaces->candidateForUser($request->user()) ?? abort(404);
        $this->supports->reply($request->user(), $candidate->id, $aspirantSupportPayment->id, $request->validated('reply'));
        return back()->with('success', 'Your reply was sent to the supporter.');
    }
}
