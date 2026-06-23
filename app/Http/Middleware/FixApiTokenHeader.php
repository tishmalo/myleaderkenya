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
        $token = $request->header('X-Api-Token') ?? $request->header('x-api-token');

        if ($token) {
            $cleanToken = trim(str_replace(['Bearer ', 'bearer '], '', $token));

            // Set the Authorization header so Sanctum can process it
            $request->headers->set('Authorization', 'Bearer ' . $cleanToken);
        }

        return $next($request);
    }
}