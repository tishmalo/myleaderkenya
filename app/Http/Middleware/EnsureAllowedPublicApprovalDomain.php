<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAllowedPublicApprovalDomain
{
    private const ALLOWED_HOSTS = [
        'myleader.co.ke',
        'nikokadi.digitallyfit.top',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $host = $this->normalizeHost($request->getHost());
        $origin = $this->hostFromHeader($request->headers->get('origin'));
        $referer = $this->hostFromHeader($request->headers->get('referer'));

        if ($this->isAllowed($host) || $this->isAllowed($origin) || $this->isAllowed($referer)) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden.'], 403);
    }

    private function hostFromHeader(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return $this->normalizeHost(parse_url($value, PHP_URL_HOST) ?: $value);
    }

    private function normalizeHost(?string $host): ?string
    {
        if (! $host) {
            return null;
        }

        return strtolower(preg_replace('/:\d+$/', '', trim($host)));
    }

    private function isAllowed(?string $host): bool
    {
        return $host !== null && in_array($host, self::ALLOWED_HOSTS, true);
    }
}
