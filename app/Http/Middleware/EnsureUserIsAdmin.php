<?php

namespace App\Http\Middleware;

use App\Contracts\Repositories\Web\CandidateRelationshipRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function __construct(private CandidateRelationshipRepositoryInterface $relationships) {}

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

            if ($user->user_type === 'aspirant' || $this->relationships->hasApprovedCandidateRelationship($user)) {
                return redirect()->route('aspirant.dashboard')
                    ->with('warning', 'Admin access is required for that page.');
            }

            return redirect()->route('landing')
                ->with('warning', 'Admin access is required for that page.');
        }

        return $next($request);
    }
}
