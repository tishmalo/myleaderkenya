<?php

namespace App\Services\Audit;

use Illuminate\Support\Str;

class AuditRedactor
{
    private const SECRET_FRAGMENTS = ['password', 'secret', 'token', 'credential', 'authorization', 'cookie', 'session', 'hash', 'provider_response'];
    private const EMAIL_FIELDS = ['email', 'email_1', 'email_2'];
    private const PHONE_FIELDS = ['phone', 'phone_1', 'phone_2', 'mobile'];
    private const ID_FIELDS = ['id_number', 'national_id', 'passport'];

    public function redact(array $values): array
    {
        $safe = [];
        foreach ($values as $field => $value) {
            $name = Str::lower((string) $field);
            if ($this->isSecret($name)) {
                $safe[$field] = '[REDACTED]';
            } elseif (in_array($name, self::EMAIL_FIELDS, true)) {
                $safe[$field] = '[MASKED]';
            } elseif (in_array($name, self::PHONE_FIELDS, true) || in_array($name, self::ID_FIELDS, true)) {
                $safe[$field] = '[MASKED]';
            } elseif (is_array($value)) {
                $safe[$field] = $this->redact($value);
            } else {
                $safe[$field] = $value;
            }
        }
        return $safe;
    }

    private function isSecret(string $field): bool
    {
        foreach (self::SECRET_FRAGMENTS as $fragment) {
            if (str_contains($field, $fragment)) return true;
        }
        return false;
    }

    private function maskEmail(mixed $value): mixed
    {
        if (! is_string($value) || ! str_contains($value, '@')) return $this->mask($value);
        [$local, $domain] = explode('@', $value, 2);
        return Str::substr($local, 0, 2).'***@'.$domain;
    }

    private function mask(mixed $value): mixed
    {
        if (! is_scalar($value) || $value === '') return $value;
        $text = (string) $value;
        return str_repeat('*', max(3, mb_strlen($text) - 4)).mb_substr($text, -4);
    }
}

