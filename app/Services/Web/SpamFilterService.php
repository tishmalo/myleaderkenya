<?php

namespace App\Services\Web;

use App\Models\SpamIpOverride;
use App\Models\SpamRule;
use App\Models\SpamSample;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SpamFilterService
{
    public function enabled(): bool
    {
        return (bool) config('spam_filter.enabled', true);
    }

    /**
     * Inspect a feature-request payload for content spam.
     *
     * @param  array<string, mixed>  $payload
     */
    public function inspect(array $payload): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        $raw = mb_strtolower(implode(' ', array_filter([
            (string) ($payload['requester_name'] ?? ''),
            (string) ($payload['requested_feature'] ?? ''),
            (string) ($payload['use_case'] ?? ''),
        ])));

        if (config('spam_filter.content.flag_html', true) && $this->containsHtml($raw)) {
            return 'html_in_message';
        }

        if (config('spam_filter.content.flag_any_url', true) && $this->containsForeignUrl($raw)) {
            return 'url_in_message';
        }

        $content = $this->normalizeContent($payload);

        foreach ($this->mergedValues('keyword') as $keyword) {
            if (Str::contains($content, mb_strtolower(trim((string) $keyword)))) {
                return 'blocked_keyword';
            }
        }

        foreach ($this->mergedValues('domain') as $domain) {
            if (Str::contains($content, mb_strtolower(trim((string) $domain))) || Str::contains($raw, mb_strtolower(trim((string) $domain)))) {
                return 'blocked_domain';
            }
        }

        foreach ($this->emailPatterns() as $pattern) {
            $email = mb_strtolower(trim((string) ($payload['email'] ?? '')));
            if ($email !== '' && preg_match($pattern, $email)) {
                return 'suspicious_email';
            }
        }

        $phone = trim((string) ($payload['phone'] ?? ''));
        $phoneAccept = config('spam_filter.content.phone_accept_regex');
        if ($phone !== '' && $phoneAccept && ! preg_match($phoneAccept, $phone)) {
            return 'suspicious_phone';
        }

        $name = mb_strtolower(trim((string) ($payload['requester_name'] ?? '')));
        foreach (config('spam_filter.content.blocked_name_phrases', []) as $phrase) {
            if ($name !== '' && Str::contains($name, mb_strtolower(trim((string) $phrase)))) {
                return 'blocked_name_phrase';
            }
        }

        if ($this->isRepeated($content)) {
            return 'repeated_text';
        }

        return null;
    }

    /**
     * Country code for an IP, or null when unknown/unavailable.
     */
    public function ipCountry(?string $ip): ?string
    {
        if (! $ip || ! $this->ipLookupEnabled()) {
            return null;
        }

        $config = config('spam_filter.ip_lookup.ip_api', []);
        $ttl = (int) ($config['cache_ttl_seconds'] ?? 86400);

        return Cache::remember('spam_ip_country:'.$ip, $ttl, function () use ($ip, $config): ?string {
            try {
                $endpoint = str_replace('{ip}', $ip, (string) ($config['endpoint'] ?? ''));

                $response = Http::timeout((int) ($config['timeout_seconds'] ?? 2))->get($endpoint);

                if (! $response->successful()) {
                    return null;
                }

                $body = $response->json();

                return ($body['status'] ?? '') === 'success'
                    ? strtoupper((string) ($body['countryCode'] ?? ''))
                    : null;
            } catch (\Throwable $e) {
                Log::warning('Spam filter IP lookup failed.', ['ip' => $ip, 'error' => $e->getMessage()]);

                return null;
            }
        });
    }

    public function isKenyan(?string $ip): bool
    {
        $country = $this->ipCountry($ip);

        return $country !== null && in_array($country, config('spam_filter.ip_lookup.kenyan_country_codes', ['KE']), true);
    }

    /**
     * IP policy for a submission: 'allow' | 'spam' | 'reject'.
     */
    public function ipPolicy(?string $ip): string
    {
        if (! $this->ipLookupEnabled() || $this->isKenyan($ip)) {
            return 'allow';
        }

        $action = $this->ipCountry($ip) === null
            ? config('spam_filter.ip_lookup.unknown_action', 'spam')
            : config('spam_filter.ip_lookup.non_kenyan_action', 'spam');

        return in_array($action, ['spam', 'reject', 'allow'], true) ? $action : 'spam';
    }

    /**
     * Whether an IP is on the blocklist (accounting for active allow overrides).
     */
    public function isIpBlocked(string $ip): bool
    {
        if (! $this->enabled() || blank($ip)) {
            return false;
        }

        if (SpamIpOverride::query()
            ->where('ip', $ip)
            ->where('action', 'allow')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists()) {
            return false;
        }

        foreach ($this->blockedIps() as $blocked) {
            if ($this->ipMatches($ip, $blocked)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove an IP from the blocklist and allow it for a limited window.
     */
    public function unblockIp(string $ip): void
    {
        SpamRule::query()->where('type', 'ip')->where('value', $ip)->delete();

        Cache::forget('spam_rules:ip');

        $ttlHours = (int) config('spam_filter.ip_challenge.override_ttl_hours', 24);

        SpamIpOverride::updateOrCreate(
            ['ip' => $ip],
            ['action' => 'allow', 'expires_at' => now()->addHours($ttlHours)]
        );
    }

    /**
     * Record a flagged submission as a spam sample for the admin hub (deduped by content hash).
     *
     * @param  array<string, mixed>  $payload
     */
    public function recordSample(array $payload, string $reason, ?string $ip = null, string $source = 'request', ?int $campaignToolRequestId = null): void
    {
        if (! config('spam_filter.record_samples', true)) {
            return;
        }

        $hash = sha1($this->normalizeContent($payload));

        try {
            SpamSample::firstOrCreate(
                ['text_hash' => $hash],
                [
                    'payload' => $payload,
                    'reason' => $reason,
                    'ip' => $ip,
                    'source' => $source,
                    'campaign_tool_request_id' => $campaignToolRequestId,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Could not record spam sample.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Suggest candidate rules from a pasted spam message.
     *
     * @return array{keyword: array<int,string>, domain: array<int,string>, email: array<int,string>, ip: array<int,string>, phone: array<int,string>}
     */
    public function suggestRules(string $text): array
    {
        $suggestions = ['keyword' => [], 'domain' => [], 'email' => [], 'ip' => [], 'phone' => []];

        preg_match_all('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', $text, $ips);
        $suggestions['ip'] = array_values(array_unique($ips[0] ?? []));

        preg_match_all('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', $text, $emails);
        $suggestions['email'] = array_values(array_unique(array_map('strtolower', $emails[0] ?? [])));

        preg_match_all('/\b(?:[a-z0-9-]+\.)+(?:com|net|org|info|biz|xyz|top|loan|gq|ml|tk|cf|ga)\b/i', $text, $domains);
        $suggestions['domain'] = array_values(array_unique(array_map('strtolower', $domains[0] ?? [])));

        preg_match_all('/\+?\d[\d\s().-]{7,19}\d/', $text, $phones);
        $suggestions['phone'] = array_values(array_unique($phones[0] ?? []));

        $lower = mb_strtolower($text);
        preg_match_all('/\b[a-z]{8,}\b/', $lower, $words);
        $stopWords = ['everybody', 'actually', 'something', 'anything', 'yourself', 'because', 'however', 'through', 'between', 'another', 'without', 'themselves', 'received'];
        $keywords = [];
        foreach (($words[0] ?? []) as $word) {
            if (in_array($word, $stopWords, true)) {
                continue;
            }
            $keywords[$word] = ($keywords[$word] ?? 0) + 1;
        }
        $suggestions['keyword'] = array_slice(
            array_keys(array_filter($keywords, fn (int $count) => $count >= 2)),
            0,
            20
        );

        return $suggestions;
    }

    private function ipLookupEnabled(): bool
    {
        return $this->enabled() && (bool) config('spam_filter.ip_lookup.enabled', true);
    }

    /**
     * @return Collection<int, string>
     */
    private function mergedValues(string $type): Collection
    {
        $configKey = match ($type) {
            'keyword' => 'spam_filter.content.blocked_keywords',
            'domain' => 'spam_filter.content.blocked_domains',
            default => null,
        };

        $defaults = $configKey ? (array) config($configKey, []) : [];

        return collect($defaults)->merge($this->dbRuleValues($type))->unique()->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function dbRuleValues(string $type): Collection
    {
        return Cache::remember('spam_rules:'.$type, 300, fn (): Collection => SpamRule::query()
            ->where('type', $type)
            ->where('enabled', true)
            ->orderBy('value')
            ->pluck('value'));
    }

    /**
     * @return array<int, string>
     */
    private function emailPatterns(): array
    {
        $patterns = config('spam_filter.content.suspicious_email_patterns', []);

        foreach ($this->dbRuleValues('email') as $rule) {
            $patterns[] = (string) $rule;
        }

        foreach ($this->dbRuleValues('regex') as $rule) {
            $patterns[] = (string) $rule;
        }

        return $patterns;
    }

    /**
     * @return array<int, string>
     */
    private function blockedIps(): array
    {
        return array_values(array_unique(array_merge(
            (array) config('spam_filter.blocked_ips', []),
            $this->dbRuleValues('ip')->all()
        )));
    }

    private function containsHtml(string $text): bool
    {
        return preg_match('/<\/?[a-z][^>]*>/i', $text) === 1;
    }

    private function containsForeignUrl(string $text): bool
    {
        if (preg_match_all('/https?:\/\/[^\s<>"\'()]+/i', $text, $matches) === false) {
            return false;
        }

        $urls = $matches[0] ?? [];

        if (! $urls) {
            return false;
        }

        $allowlist = array_map(fn ($d) => mb_strtolower(trim((string) $d)), (array) config('spam_filter.content.url_allowlist', []));

        foreach ($urls as $url) {
            $host = mb_strtolower((string) parse_url($url, PHP_URL_HOST));

            if ($host === '') {
                continue;
            }

            $allowed = false;
            foreach ($allowlist as $domain) {
                if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                    $allowed = true;
                    break;
                }
            }

            if (! $allowed) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function normalizeContent(array $payload): string
    {
        $parts = array_filter([
            (string) ($payload['requester_name'] ?? ''),
            (string) ($payload['requested_feature'] ?? ''),
            (string) ($payload['use_case'] ?? ''),
        ], fn ($value) => trim((string) $value) !== '');

        $text = mb_strtolower(implode(' ', $parts));

        return trim(strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function isRepeated(string $content): bool
    {
        $threshold = (int) config('spam_filter.content.repeated_phrase_threshold', 5);

        if ($threshold <= 0) {
            return false;
        }

        preg_match_all('/\b[a-z]{6,}\b/', $content, $matches);
        $counts = array_count_values($matches[0] ?? []);

        foreach ($counts as $count) {
            if ($count >= $threshold) {
                return true;
            }
        }

        return false;
    }

    private function ipMatches(string $ip, string $blocked): bool
    {
        $blocked = trim($blocked);

        if ($blocked === $ip) {
            return true;
        }

        if (str_contains($blocked, '/')) {
            [$subnet, $bits] = array_pad(explode('/', $blocked, 2), 2, null);

            if (filter_var($subnet, FILTER_VALIDATE_IP) === false || ! ctype_digit((string) $bits)) {
                return false;
            }

            return $this->ipInCidr($ip, $subnet, (int) $bits);
        }

        return false;
    }

    private function ipInCidr(string $ip, string $subnet, int $bits): bool
    {
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $mask = $bits === 0 ? 0 : (-1 << (32 - $bits)) & 0xFFFFFFFF;

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}