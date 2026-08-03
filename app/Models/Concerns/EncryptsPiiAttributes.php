<?php

namespace App\Models\Concerns;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

trait EncryptsPiiAttributes
{
    public static function piiHash($value): ?string
    {
        $normalized = static::normalizePiiValue($value);

        if ($normalized === null) {
            return null;
        }

        return hash_hmac('sha256', $normalized, (string) config('app.key'));
    }

    protected static function normalizePiiValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return Str::lower($value);
    }

    protected function encryptPiiValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            Crypt::decryptString((string) $value);

            return (string) $value;
        } catch (DecryptException) {
            return Crypt::encryptString((string) $value);
        }
    }

    protected function decryptPiiValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (DecryptException) {
            return (string) $value;
        }
    }
}
