<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\SmtpRepositoryInterface;

class SmtpRepository implements SmtpRepositoryInterface
{
    public function setEnvironmentValue(string $key, string $value): void
    {
        $path = base_path('.env');

        if (!file_exists($path)) {
            return;
        }

        $value   = '"' . trim($value) . '"';
        $current = file_get_contents($path);

        if (preg_match("/^{$key}=.*/m", $current)) {
            file_put_contents($path, preg_replace("/^{$key}=.*/m", "{$key}={$value}", $current));
        } else {
            file_put_contents($path, $current . "\n{$key}={$value}");
        }
    }
}
