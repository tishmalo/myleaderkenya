<?php

namespace App\Support;

use Illuminate\Support\Str;

final class PiiProtection
{
    public static function emailBlindIndex(?string $email): ?string
    {
        if (blank($email)) {
            return null;
        }

        return hash_hmac(
            'sha256',
            Str::lower(trim((string) $email)),
            self::indexKey()
        );
    }

    private static function indexKey(): string
    {
        $key = (string) (config('pii.index_key') ?: config('app.key'));

        if ($key === '') {
            throw new \RuntimeException('A PII blind-index key is not configured.');
        }

        if (Str::startsWith($key, 'base64:')) {
            $decoded = base64_decode(Str::after($key, 'base64:'), true);

            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $key;
    }
}
