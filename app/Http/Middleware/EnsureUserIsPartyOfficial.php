<?php
namespace App\Http\Middleware;
use App\Services\Web\PoliticalPartyAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class EnsureUserIsPartyOfficial { public function __construct(private PoliticalPartyAccessService $access){} public function handle(Request $request, Closure $next): Response { if(!$request->user()) return redirect()->route('login'); if(!$this->access->membership($request->user())) return redirect()->route('landing')->with('warning','Active political party access is required.'); return $next($request); } }