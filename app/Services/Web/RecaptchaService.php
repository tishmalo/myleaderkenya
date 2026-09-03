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
     * Verify a reCAPTCHA response token server-side.
     * Supports both v3 (score present) and v2 checkbox (no score).
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

        if (! (bool) ($body['success'] ?? false)) {
            return false;
        }

        // v2 checkbox responses omit "score"; only enforce the threshold when present.
        if (! array_key_exists('score', $body)) {
            return true;
        }

        return (float) $body['score'] >= self::MIN_SCORE;
    }
}
