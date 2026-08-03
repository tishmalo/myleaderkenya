<?php
namespace App\Http\Middleware;
use App\Services\Web\AspirantWorkspaceService; use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class EnsureUserOwnsActiveCandidate {
    public function __construct(private AspirantWorkspaceService $workspaces) {}
    public function handle(Request $request, Closure $next): Response { $user=$request->user(); if(!$user)return redirect()->route('login'); $candidate=$this->workspaces->candidateForUser($user); abort_unless($candidate && (int)$candidate->user_id===(int)$user->id && $user->user_type==='aspirant',403); $request->attributes->set('audit_candidate',$candidate); return $next($request); }
}
