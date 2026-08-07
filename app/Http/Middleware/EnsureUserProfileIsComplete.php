<?php

namespace App\Http\Middleware;

use App\Services\Web\UserProfileService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserProfileIsComplete
{
    public function __construct(private UserProfileService $profiles) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isAdmin() && ! $this->profiles->isComplete($user)) {
            return redirect()
                ->route('account.profile.edit')
                ->with('warning', 'Complete and verify your profile before using your dashboard tools.');
        }

        return $next($request);
    }
}