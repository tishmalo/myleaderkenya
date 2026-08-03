<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventStaleCsrfPages
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethodSafe() || ! method_exists($response, 'getContent')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type');
        $content = (string) $response->getContent();

        if (str_contains($contentType, 'text/html') && str_contains($content, 'name=') && str_contains($content, '_token')) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}