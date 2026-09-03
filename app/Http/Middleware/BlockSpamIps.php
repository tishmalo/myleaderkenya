<?php

namespace App\Http\Middleware;

use App\Services\Admin\SettingService;
use App\Services\Web\SpamFilterService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSpamIps
{
    public function __construct(
        private SpamFilterService $spamFilter,
        private SettingService $settingService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        if (! $ip || $this->isExcludedPath($request->path())) {
            return $next($request);
        }

        if ($this->spamFilter->isIpBlocked($ip)) {
            return response()->view('errors.bot-403', [
                'ip' => $ip,
                'recaptchaSiteKey' => $this->settingService->recaptchaSiteKey(),
                'intended' => $request->fullUrl(),
            ], 403);
        }

        return $next($request);
    }

    private function isExcludedPath(string $path): bool
    {
        $path = '/'.trim($path, '/');

        foreach ((array) config('spam_filter.ip_challenge.excluded_paths', []) as $excluded) {
            $excluded = '/'.trim((string) $excluded, '/');

            if ($excluded === '' || $path === $excluded || str_starts_with($path, $excluded.'/')) {
                return true;
            }
        }

        return false;
    }
}