<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAspirant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated'], 401)
                : redirect()->route('login');
        }

        if ($user->user_type !== 'aspirant') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Aspirant access required.'], 403);
            }

            if (($user->role ?? null) === 'admin') {
                return redirect()->route('dashboard')
                    ->with('warning', 'Aspirant access is required for that page.');
            }

            return redirect()->route('landing')
                ->with('warning', 'Aspirant access is required for that page.');
        }

        return $next($request);
    }
}
