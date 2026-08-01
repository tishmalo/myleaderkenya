<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class EnsurePulseEngineApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.pulse_engine.api_key');
        $provided = (string) $request->header('X-Api-Key', '');
        if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
            return new JsonResponse(['message' => 'Unauthenticated.'], 401);
        }
        return $next($request);
    }
}
