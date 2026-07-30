<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StartAspirantImpersonationRequest;
use App\Http\Requests\Admin\StopAspirantImpersonationRequest;
use App\Models\Candidate;
use App\Models\User;
use App\Services\Admin\AspirantImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AspirantImpersonationController extends Controller
{
    public function __construct(private AspirantImpersonationService $impersonationService) {}

    public function start(StartAspirantImpersonationRequest $request, Candidate $candidate, User $user): RedirectResponse
    {
        $admin = $request->user();

        if (! $this->impersonationService->canImpersonate($admin, $user, $candidate)) {
            return back()->with('warning', 'This user cannot access that aspirant dashboard.');
        }

        $request->session()->put([
            'impersonator_admin_id' => $admin->id,
            'impersonator_return_url' => url()->previous(),
            'impersonated_candidate_id' => $candidate->id,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('aspirant.dashboard')
            ->with('success', 'You are now viewing the aspirant dashboard as ' . $user->name . '.');
    }

    public function stop(StopAspirantImpersonationRequest $request): RedirectResponse
    {
        $admin = User::find($request->session()->get('impersonator_admin_id'));
        $returnUrl = $request->session()->get('impersonator_return_url') ?: route('dashboard');

        $request->session()->forget([
            'impersonator_admin_id',
            'impersonator_return_url',
            'impersonated_candidate_id',
        ]);

        if ($admin) {
            Auth::login($admin);
            $request->session()->regenerate();

            return redirect($returnUrl)->with('success', 'Returned to admin account.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('warning', 'Admin session could not be restored. Please log in again.');
    }
}
