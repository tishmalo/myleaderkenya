<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\SettingRepositoryInterface;

class SettingService
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository
    ) {}

    /**
     * Get the donation settings (why text and whatsapp link).
     */
    public function getDonateSettings(): array
    {
        $donateWhyText = $this->settingRepository->firstOrCreate(
            'donation_why_text',
            'We are non partisan. We purely depend on donations to help us remain neutral. Donations are used to fund the hosting, development, security and the setup of county, constituency and ward youth employment of the secretariate network to help mobilize people to verify and register to vote. Once you donate, you join our "Donor Club" where you receive updates.'
        );

        $whatsappLink = $this->settingRepository->firstOrCreate(
            'donation_whatsapp_link',
            'https://chat.whatsapp.com/example'
        );

        return [
            'donateWhyText' => $donateWhyText,
            'whatsappLink' => $whatsappLink,
        ];
    }

    /**
     * Update the donation settings.
     */
    public function updateDonateSettings(array $data): void
    {
        if (isset($data['donation_why_text'])) {
            $this->settingRepository->updateOrCreate('donation_why_text', $data['donation_why_text']);
        }

        if (isset($data['donation_whatsapp_link'])) {
            $this->settingRepository->updateOrCreate('donation_whatsapp_link', $data['donation_whatsapp_link']);
        }
    }
}
