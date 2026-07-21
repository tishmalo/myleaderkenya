<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\Admin\CandidateTokenPackageRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\TokenPurchaseRequest;
use App\Models\CandidateTokenPurchase;
use App\Models\CandidateTokenTransaction;
use App\Models\PaymentMethod;
use App\Services\Web\AspirantTokenService;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AspirantTokenController extends Controller
{
    public function __construct(
        private AspirantWorkspaceService $workspaceService,
        private AspirantTokenService $tokenService,
        private CandidateTokenPackageRepositoryInterface $packages
    ) {}

    public function index()
    {
        $candidate = $this->workspaceService->candidateForUser(request()->user());

        if (! $candidate) {
            return redirect('/aspirant/dashboard')->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        return view('aspirants.tokens.index', [
            'candidate' => $candidate,
            'wallet' => $this->tokenService->walletForCandidate($candidate),
            'packages' => $this->tokenService->activePackages(),
            'rates' => $this->tokenService->activeRates(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
            'purchases' => CandidateTokenPurchase::with('paymentMethod')->where('candidate_id', $candidate->id)->latest()->take(10)->get(),
            'transactions' => CandidateTokenTransaction::where('candidate_id', $candidate->id)->latest()->take(20)->get(),
        ]);
    }

    public function purchase(TokenPurchaseRequest $request): RedirectResponse
    {
        $candidate = $this->workspaceService->candidateForUser(request()->user());

        if (! $candidate) {
            return redirect('/aspirant/dashboard')->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $package = $this->packages->findActive((int) $request->validated('candidate_token_package_id'));
        $this->tokenService->purchaseTokens($candidate, $request->user(), $package, $request->validated());

        return redirect()->route('aspirant.tokens.index')
            ->with('success', number_format($package->token_amount) . ' tokens credited to your campaign wallet.');
    }
}

