<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class HomepageCache
{
    public const TTL_SECONDS = 7200;
    private const VERSION_KEY = 'homepage:cache-version';

    public static function version(): int
    {
        $version = Cache::get(self::VERSION_KEY);

        if (! is_numeric($version)) {
            $version = 1;
            Cache::forever(self::VERSION_KEY, $version);
        }

        return (int) $version;
    }

    public static function key(string $name, array $parts = []): string
    {
        $suffix = $parts ? ':' . md5(json_encode($parts)) : '';

        return 'homepage:v' . self::version() . ':' . $name . $suffix;
    }

    public static function ttl(): int
    {
        return self::TTL_SECONDS;
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }
}
