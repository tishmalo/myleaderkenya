<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\Admin\CandidateTokenPackageRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\TokenPurchaseRequest;
use App\Models\CandidateTokenPurchase;
use App\Models\CandidateTokenTransaction;
use App\Services\Web\AspirantTokenService;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

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
            'checkoutContact' => $this->checkoutContact($candidate, request()->user()),
            'purchases' => CandidateTokenPurchase::with('paymentMethod')->where('candidate_id', $candidate->id)->latest()->take(10)->get(),
            'transactions' => CandidateTokenTransaction::where('candidate_id', $candidate->id)->latest()->take(20)->get(),
        ]);
    }

    public function purchase(TokenPurchaseRequest $request): RedirectResponse
    {
        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect('/aspirant/dashboard')->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $package = $this->packages->findActive((int) $request->validated('candidate_token_package_id'));

        try {
            $checkoutUrl = $this->tokenService->startIpayPurchase($candidate, $request->user(), $package, $request->validated());
        } catch (Throwable $exception) {
            return redirect()->route('aspirant.tokens.index')->with('warning', $exception->getMessage());
        }

        return redirect()->away($checkoutUrl);
    }

    public function ipayCallback(Request $request): RedirectResponse
    {
        try {
            $result = $this->tokenService->completeIpayCallback($request->query());
        } catch (Throwable $exception) {
            $result = ['status' => 'failed', 'message' => $exception->getMessage()];
        }

        $flashKey = $result['status'] === 'success' ? 'success' : 'warning';

        return redirect()->route('aspirant.tokens.index')->with($flashKey, $result['message']);
    }

    private function checkoutContact($candidate, $user): array
    {
        return [
            'phone' => old('phone', $user->phone ?: ($candidate->phone_1 ?: $candidate->phone)),
            'email' => old('email', $user->email ?: ($candidate->email_1 ?: $candidate->email)),
        ];
    }
}

