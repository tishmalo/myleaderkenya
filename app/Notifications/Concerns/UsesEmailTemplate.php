<?php

namespace App\Notifications\Concerns;

use App\Services\Admin\SettingService;

trait UsesEmailTemplate
{
    protected function template(string $key): ?array
    {
        return app(SettingService::class)->notificationTemplate($key);
    }

    protected function fill(string $text, array $map): string
    {
        return strtr($text, $map);
    }
}
