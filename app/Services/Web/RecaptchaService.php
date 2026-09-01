<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Admin\SettingRepositoryInterface;
use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    public const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public const MIN_SCORE = 0.5;

    public function __construct(
        private SettingRepositoryInterface $settingRepository
    ) {}

    public function enabled(): bool
    {
        return $this->siteKey() !== '' && $this->secretKey() !== '';
    }

    public function siteKey(): string
    {
        return trim((string) $this->settingRepository->getByKey('recaptcha_site_key'));
    }

    public function secretKey(): string
    {
        return trim((string) $this->settingRepository->getByKey('recaptcha_secret_key'));
    }

    /**
     * Verify a reCAPTCHA v3 response token server-side.
     */
    public function verify(?string $token): bool
    {
        if (! $this->enabled() || blank($token)) {
            return false;
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post(self::VERIFY_URL, [
                'secret' => $this->secretKey(),
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);

        if (! $response->successful()) {
            return false;
        }

        $body = $response->json();

        return (bool) ($body['success'] ?? false)
            && (float) ($body['score'] ?? 0.0) >= self::MIN_SCORE;
    }
}
