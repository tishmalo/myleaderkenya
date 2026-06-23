<?php

namespace App\Contracts\Repositories\Admin;

interface SettingRepositoryInterface
{
    /**
     * Get a setting value by key or create with default.
     */
    public function firstOrCreate(string $key, string $defaultValue): string;

    /**
     * Update a setting value by key or create if not exists.
     */
    public function updateOrCreate(string $key, string $value): void;
    
    /**
     * Get a setting by key, returns null if not found.
     */
    public function getByKey(string $key): ?string;
}
