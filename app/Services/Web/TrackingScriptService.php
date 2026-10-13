<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Admin\SettingRepositoryInterface;
use App\Support\TrackingSettings;
use Illuminate\Support\Facades\Cache;

/**
 * Reads and writes the head tracking snippets an admin manages from
 * Settings -> Tracking & Ads.
 *
 * The assembled markup is cached per area because it runs in the head of every
 * page; saving the settings clears it.
 */
class TrackingScriptService
{
    private const CACHE_PREFIX = 'tracking:head:';

    public function __construct(
        private SettingRepositoryInterface $settingRepository
    ) {}

    /**
     * @return array<string, string>
     */
    public function settings(): array
    {
        $stored = [];

        foreach (TrackingSettings::defaults() as $key => $default) {
            $stored[$key] = $this->settingRepository->firstOrCreate($key, $default);
        }

        return $stored;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function update(array $input): void
    {
        foreach (TrackingSettings::normalize($input, $this->settings()) as $key => $value) {
            $this->settingRepository->updateOrCreate($key, $value);
        }

        $this->forget();
    }

    /**
     * The snippets for one area: the public site or the dashboards. Returns an
     * empty string when the area is switched off or nothing is configured.
     */
    public function headMarkup(string $area = TrackingSettings::AREA_PUBLIC): string
    {
        return (string) Cache::rememberForever(
            self::CACHE_PREFIX.$area,
            function () use ($area): string {
                $settings = $this->settings();

                if (! TrackingSettings::isEnabledFor($settings, $area)) {
                    return '';
                }

                return TrackingSettings::headMarkup($settings);
            }
        );
    }

    public function forget(): void
    {
        foreach (TrackingSettings::areas() as $area) {
            Cache::forget(self::CACHE_PREFIX.$area);
        }
    }
}
