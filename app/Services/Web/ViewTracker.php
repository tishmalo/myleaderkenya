<?php

namespace App\Services\Web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Counts one view per visitor per item per day.
 *
 * Deliberately conservative: crawlers, link-preview fetchers and staff previews
 * are ignored, and a visitor refreshing the page all afternoon still counts
 * once, so the numbers reflect reach rather than reloads.
 */
class ViewTracker
{
    /**
     * Substrings that identify an automated caller in the user agent.
     *
     * @var list<string>
     */
    private const BOT_SIGNATURES = [
        'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'http-client', 'python-requests',
        'headless', 'phantom', 'lighthouse', 'pagespeed', 'monitor', 'uptime', 'pingdom',
        'facebookexternalhit', 'whatsapp', 'telegrambot', 'preview', 'embedly', 'quora link',
    ];

    /**
     * Records a view on the model and returns whether it counted.
     *
     * The model's own `views` attribute is kept in step, so a page can render
     * the fresh total in the same request.
     */
    public function track(Model $viewable, Request $request): bool
    {
        if (! $this->shouldCount($viewable, $request)) {
            return false;
        }

        $key = $this->cacheKey($viewable, $request);

        if (! Cache::add($key, true, now()->endOfDay())) {
            return false;
        }

        $viewable->newQuery()->whereKey($viewable->getKey())->increment('views');
        $viewable->setAttribute('views', (int) $viewable->getAttribute('views') + 1);
        $viewable->syncOriginalAttribute('views');

        return true;
    }

    private function shouldCount(Model $viewable, Request $request): bool
    {
        if (! $request->isMethod('GET') || $viewable->getKey() === null) {
            return false;
        }

        if ($this->isAutomated($request)) {
            return false;
        }

        // Staff opening their own pages are not the audience.
        $viewer = $request->user();

        return ! ($viewer !== null && $viewer->isAdmin());
    }

    private function isAutomated(Request $request): bool
    {
        $agent = Str::lower((string) $request->userAgent());

        if (trim($agent) === '') {
            return true;
        }

        foreach (self::BOT_SIGNATURES as $signature) {
            if (str_contains($agent, $signature)) {
                return true;
            }
        }

        return false;
    }

    private function cacheKey(Model $viewable, Request $request): string
    {
        $viewer = $request->user();

        $visitor = $viewer !== null
            ? 'user:'.$viewer->getKey()
            : 'ip:'.sha1((string) $request->ip());

        return implode(':', ['view', $viewable->getMorphClass(), (string) $viewable->getKey(), $visitor]);
    }
}
