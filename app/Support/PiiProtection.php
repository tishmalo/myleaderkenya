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

        return hash('sha256', Str::lower(trim((string) $email)));
    }
}
