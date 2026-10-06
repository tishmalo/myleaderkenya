<?php

namespace App\Services\Web;

use App\Models\CampaignTool;
use App\Models\Candidate;
use App\Models\Coalition;
use App\Models\County;
use App\Models\Event;
use App\Models\NewsArticle;
use App\Models\PoliticalParty;
use App\Models\Poll;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Builds the public sitemap.xml (an index plus one file per section, chunked
 * past 10,000 URLs). Output is cached for a day behind a version number, so
 * the admin "Regenerate" button is just a version bump - stale files age out
 * on their own and nothing ever needs deleting.
 */
class SitemapService
{
    public const CHUNK = 10000;

    private const TTL = 86400;

    private const PAGE = 500;

    public function index(): string
    {
        return Cache::remember(
            $this->key('index'),
            self::TTL,
            fn (): string => $this->renderIndex($this->files())
        );
    }

    public function file(string $name): ?string
    {
        $file = $this->files()->firstWhere('name', $name);

        if ($file === null) {
            return null;
        }

        return Cache::remember(
            $this->key('file.'.$name),
            self::TTL,
            fn (): string => $this->renderUrls(
                $this->sections()[$file['section']]['urls']($file['chunk']),
                $this->sections()[$file['section']],
            ),
        );
    }

    public function version(): int
    {
        return (int) Cache::get($this->key('version'), 1);
    }

    public function regenerate(): void
    {
        Cache::put($this->key('version'), $this->version() + 1, self::TTL);
    }

    /**
     * Every sitemap file: section, chunk number, URL count and freshness for
     * the admin page and the index. Counts are cheap aggregates, cached
     * output is never counted twice.
     *
     * @return Collection<int, array{name: string, section: string, chunk: int, urls: int, lastmod: ?string}>
     */
    public function files(): Collection
    {
        $files = collect();

        foreach ($this->sections() as $section => $definition) {
            $total = $definition['count']();

            if ($total === 0) {
                continue;
            }

            foreach (range(1, (int) ceil($total / self::CHUNK)) as $chunk) {
                $files->push([
                    'name' => $chunk === 1 && $total <= self::CHUNK ? $section : $section.'-'.$chunk,
                    'section' => $section,
                    'chunk' => $chunk,
                    'urls' => min(self::CHUNK, $total - ($chunk - 1) * self::CHUNK),
                    'lastmod' => $definition['lastmod'](),
                ]);
            }
        }

        return $files->values();
    }

    public function totalUrls(): int
    {
        return $this->files()->sum('urls');
    }

    private function key(string $suffix): string
    {
        if ($suffix === 'version') {
            return 'sitemap:version';
        }

        return 'sitemap:v'.$this->version().':'.$suffix;
    }

    /**
     * One entry per sitemap section. Queries stay narrow (route key plus
     * updated_at) and stream in 500-row pages so a big aspirant roll never
     * exhausts memory. Only URLs that return 200 for guests are listed - the
     * login-walled polls index, for example, is deliberately left out while
     * individual live poll pages are in.
     */
    private function sections(): array
    {
        return [
            'static' => [
                'changefreq' => 'daily', 'priority' => '1.0',
                'count' => fn (): int => count($this->staticUrls()),
                'lastmod' => fn (): ?string => null,
                'urls' => fn (int $chunk): array => $this->staticUrls(),
            ],
            'counties' => [
                'changefreq' => 'daily', 'priority' => '0.8',
                'count' => fn (): int => 1 + County::query()->count(),
                'lastmod' => fn (): ?string => $this->maxUpdatedAt(County::query()),
                'urls' => fn (int $chunk): array => array_merge(
                    [$this->url(route('counties.public'), null, 'daily', '0.9')],
                    $this->chunkModels(
                        County::query()->orderBy('id'),
                        $chunk,
                        fn (County $county): array => $this->url(
                            route('county.show', $county),
                            $county->updated_at,
                            'daily',
                            '0.8',
                        ),
                    ),
                ),
            ],
            'aspirants' => [
                'changefreq' => 'daily', 'priority' => '0.7',
                'count' => fn (): int => 1 + Candidate::query()->where('approval_status', 'approved')->count(),
                'lastmod' => fn (): ?string => $this->maxUpdatedAt(
                    Candidate::query()->where('approval_status', 'approved')
                ),
                'urls' => fn (int $chunk): array => array_merge(
                    [$this->url(route('aspirants.public'), null, 'daily', '0.8')],
                    $this->chunkModels(
                        Candidate::query()->where('approval_status', 'approved')->orderBy('id'),
                        $chunk,
                        fn (Candidate $candidate): array => $this->url(
                            route('aspirants.show', $candidate),
                            $candidate->updated_at,
                            'weekly',
                            '0.7',
                        ),
                    ),
                ),
            ],
            'parties' => [
                'changefreq' => 'weekly', 'priority' => '0.7',
                'count' => fn (): int => 1 + PoliticalParty::query()->published()->count(),
                'lastmod' => fn (): ?string => $this->maxUpdatedAt(PoliticalParty::query()->published()),
                'urls' => fn (int $chunk): array => array_merge(
                    [$this->url(route('parties.public'), null, 'weekly', '0.7')],
                    $this->chunkModels(
                        PoliticalParty::query()->published()->orderBy('id'),
                        $chunk,
                        fn (PoliticalParty $party): array => $this->url(
                            route('parties.show', $party),
                            $party->updated_at,
                            'weekly',
                            '0.7',
                        ),
                    ),
                ),
            ],
            'coalitions' => [
                'changefreq' => 'weekly', 'priority' => '0.6',
                'count' => fn (): int => 1 + Coalition::query()->published()->count(),
                'lastmod' => fn (): ?string => $this->maxUpdatedAt(Coalition::query()->published()),
                'urls' => fn (int $chunk): array => array_merge(
                    [$this->url(route('coalitions.public'), null, 'weekly', '0.6')],
                    $this->chunkModels(
                        Coalition::query()->published()->orderBy('id'),
                        $chunk,
                        fn (Coalition $coalition): array => $this->url(
                            route('coalitions.show', $coalition),
                            $coalition->updated_at,
                            'weekly',
                            '0.6',
                        ),
                    ),
                ),
            ],
            'news' => [
                'changefreq' => 'daily', 'priority' => '0.7',
                'count' => fn (): int => 1 + NewsArticle::query()->where('status', 'published')->count(),
                'lastmod' => fn (): ?string => $this->maxUpdatedAt(
                    NewsArticle::query()->where('status', 'published')
                ),
                'urls' => fn (int $chunk): array => array_merge(
                    [$this->url(route('news.public'), null, 'daily', '0.8')],
                    $this->chunkModels(
                        NewsArticle::query()->where('status', 'published')->orderBy('id'),
                        $chunk,
                        fn (NewsArticle $article): array => $this->url(
                            route('news.public.show', $article->slug),
                            $article->updated_at ?? $article->published_at,
                            'weekly',
                            '0.7',
                        ),
                    ),
                ),
            ],
            'events' => [
                'changefreq' => 'daily', 'priority' => '0.7',
                'count' => fn (): int => 1 + Event::query()->active()->approved()->count(),
                'lastmod' => fn (): ?string => $this->maxUpdatedAt(
                    Event::query()->active()->approved()
                ),
                'urls' => fn (int $chunk): array => array_merge(
                    [$this->url(route('events.public'), null, 'daily', '0.8')],
                    $this->chunkModels(
                        Event::query()->active()->approved()->orderBy('id'),
                        $chunk,
                        fn (Event $event): array => $this->url(
                            route('events.show', $event),
                            $event->updated_at,
                            'weekly',
                            '0.7',
                        ),
                    ),
                ),
            ],
            'polls' => [
                'changefreq' => 'daily', 'priority' => '0.6',
                'count' => fn (): int => Poll::query()->active()->count(),
                'lastmod' => fn (): ?string => $this->maxUpdatedAt(Poll::query()->active()),
                'urls' => fn (int $chunk): array => $this->chunkModels(
                    Poll::query()->active()->orderBy('id'),
                    $chunk,
                    fn (Poll $poll): array => $this->url(
                        route('poll.show', $poll->slug),
                        $poll->updated_at,
                        'daily',
                        '0.6',
                    ),
                ),
            ],
            'tools' => [
                'changefreq' => 'weekly', 'priority' => '0.6',
                'count' => fn (): int => 1 + CampaignTool::query()->published()->count(),
                'lastmod' => fn (): ?string => $this->maxUpdatedAt(CampaignTool::query()->published()),
                'urls' => fn (int $chunk): array => array_merge(
                    [$this->url(route('campaign-tools.public'), null, 'weekly', '0.6')],
                    $this->chunkModels(
                        CampaignTool::query()->published()->orderBy('id'),
                        $chunk,
                        fn (CampaignTool $tool): array => $this->url(
                            route('campaign-tools.show', $tool),
                            $tool->updated_at,
                            'weekly',
                            '0.6',
                        ),
                    ),
                ),
            ],
        ];
    }

    /**
     * @return array<int, array{loc: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    private function staticUrls(): array
    {
        return [
            $this->url(route('landing'), null, 'daily', '1.0'),
            $this->url(route('about.public'), null, 'monthly', '0.5'),
            $this->url(route('live-stats.public'), null, 'daily', '0.6'),
            $this->url(route('download-app.public'), null, 'monthly', '0.5'),
            $this->url(route('contact.public'), null, 'monthly', '0.5'),
            $this->url(route('privacy'), null, 'yearly', '0.3'),
        ];
    }

    private function url(string $loc, mixed $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod ? date('Y-m-d', strtotime((string) $lastmod)) : null,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    /**
     * @return array<int, array{loc: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    private function chunkModels(mixed $query, int $chunk, callable $map): array
    {
        $urls = [];
        $offset = ($chunk - 1) * self::CHUNK;
        $remaining = self::CHUNK;
        $page = (int) floor($offset / self::PAGE) + 1;
        $skipInPage = $offset % self::PAGE;

        while ($remaining > 0) {
            $models = (clone $query)->forPage($page, self::PAGE)->get();

            if ($models->isEmpty()) {
                break;
            }

            foreach ($models->slice($skipInPage)->take($remaining) as $model) {
                $urls[] = $map($model);
                $remaining--;
            }

            $skipInPage = 0;
            $page++;
        }

        return $urls;
    }

    private function maxUpdatedAt(mixed $query): ?string
    {
        $max = (clone $query)->max('updated_at');

        return $max ? date('Y-m-d', strtotime((string) $max)) : null;
    }

    private function renderIndex(Collection $files): string
    {
        $items = $files->map(fn (array $file): string => '  <sitemap>'.
            '<loc>'.e(route('sitemap.file', ['file' => $file['name']])).'</loc>'.
            ($file['lastmod'] ? '<lastmod>'.e($file['lastmod']).'</lastmod>' : '').
            '</sitemap>')->implode("\n");

        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n".
            $items."\n".
            '</sitemapindex>';
    }

    private function renderUrls(array $urls, array $section): string
    {
        $items = collect($urls)->map(fn (array $url): string => '  <url>'.
            '<loc>'.e($url['loc']).'</loc>'.
            ($url['lastmod'] ? '<lastmod>'.e($url['lastmod']).'</lastmod>' : '').
            '<changefreq>'.e($url['changefreq']).'</changefreq>'.
            '<priority>'.e($url['priority']).'</priority>'.
            '</url>')->implode("\n");

        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n".
            $items."\n".
            '</urlset>';
    }
}
