<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || empty($permissions)) {
            abort(403);
        }

        foreach ($permissions as $permission) {
            if ($user->canAccess($permission)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this module.');
    }
}
