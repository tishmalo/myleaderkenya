<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Web\DashboardDestinationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private DashboardDestinationService $dashboardDestination) {}

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $dashboardUrl = $this->dashboardDestination->urlFor(
            $request->user(),
            absolute: false,
        );

        return redirect()->intended($dashboardUrl);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if ($request->session()->has('impersonator_admin_id')) {
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
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

