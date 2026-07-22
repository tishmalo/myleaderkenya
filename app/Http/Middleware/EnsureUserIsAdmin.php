<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated'], 401)
                : redirect()->route('login');
        }

        if (! $user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
            }

            if ($user->user_type === 'aspirant') {
                return redirect()->route('aspirant.dashboard')
                    ->with('warning', 'Admin access is required for that page.');
            }

            return redirect()->route('landing')
                ->with('warning', 'Admin access is required for that page.');
        }

        return $next($request);
    }
}
