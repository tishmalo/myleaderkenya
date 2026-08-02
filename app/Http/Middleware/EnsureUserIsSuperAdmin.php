<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated'], 401)
                : redirect()->route('login');
        }

        if (! $user->isSuperAdmin()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthorized. Super admin access required.'], 403)
                : redirect()->route('dashboard')->with('warning', 'Super admin access is required for that page.');
        }

        return $next($request);
    }
}
