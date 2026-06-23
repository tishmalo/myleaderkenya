<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\SettingRepositoryInterface;
use App\Models\Setting;

class SettingRepository implements SettingRepositoryInterface
{
    public function firstOrCreate(string $key, string $defaultValue): string
    {
        return Setting::firstOrCreate(
            ['key' => $key],
            ['value' => $defaultValue]
        )->value;
    }

    public function updateOrCreate(string $key, string $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
    
    public function getByKey(string $key): ?string
    {
        return Setting::where('key', $key)->value('value');
    }
}
