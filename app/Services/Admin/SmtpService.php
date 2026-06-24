<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\SmtpRepositoryInterface;
use Illuminate\Support\Facades\Artisan;

class SmtpService
{
    public function __construct(
        private SmtpRepositoryInterface $smtpRepository
    ) {}

    /**
     * Persist SMTP settings to the .env file and clear config/cache.
     *
     * @param array<string, string> $settings  Keyed by env variable name (e.g. MAIL_HOST)
     */
    public function updateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->smtpRepository->setEnvironmentValue(strtoupper($key), $value);
        }

        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }
}
