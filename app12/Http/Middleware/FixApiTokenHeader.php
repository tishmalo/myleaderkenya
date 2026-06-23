<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class FixApiTokenHeader
{
    public function handle(Request $request, Closure $next): Response
{
    $token = $request->header('X-Api-Token') 
        ?? $request->header('x-api-token')
        ?? $request->server('HTTP_X_API_TOKEN');

    if ($token) {
        $cleanToken = trim(str_replace(['Bearer ', 'bearer '], '', $token));
        
        $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($cleanToken);
        
    

        if ($accessToken) {
            $user = $accessToken->tokenable;
            auth()->guard('sanctum')->setUser($user);
            auth()->setUser($user);
            
            
        }
    }

    return $next($request);
}
}