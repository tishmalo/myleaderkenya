<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\SettingRepositoryInterface;
use Illuminate\Support\Arr;

class SettingService
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository
    ) {}

    public const FRONTEND_PAGES = [
        'about-us' => [
            'label' => 'About Us',
            'route' => 'about.public',
            'defaults' => [
                'title' => 'About Us',
                'hero_title' => 'About Tuko Kadi',
                'excerpt' => 'A non-partisan youth initiative helping more Kenyans verify, register, and participate in democracy.',
                'content' => 'Tuko Kadi is a non-partisan youth initiative dedicated to increasing voter registration among young Kenyans ahead of the 2027 General Election. We believe that when the youth actively participate in democracy, Kenya becomes stronger, more accountable, and truly representative of its future leaders.',
                'meta_title' => 'About Us - Tuko Kadi',
                'meta_description' => 'Learn about Tuko Kadi, a non-partisan Kenya voter registration and civic participation initiative.',
                'cta_label' => 'Join Now',
                'cta_url' => '/?auth=register',
            ],
        ],
        'live-stats' => [
            'label' => 'Live Stats',
            'route' => 'live-stats.public',
            'defaults' => [
                'title' => 'Live Stats',
                'hero_title' => 'Live Voter Stats',
                'excerpt' => 'Track voter registration momentum, verified voters, and county-level activity in real time.',
                'content' => 'Use these live stats to understand voter registration progress, county activity, and participation trends across the Tuko Kadi network.',
                'meta_title' => 'Live Voter Stats - Tuko Kadi',
                'meta_description' => 'View live Tuko Kadi voter registration statistics and county-level participation data.',
                'cta_label' => 'View Aspirants',
                'cta_url' => '/aspirants',
            ],
        ],
        'download-app' => [
            'label' => 'Download App',
            'route' => 'download-app.public',
            'defaults' => [
                'title' => 'Download App',
                'hero_title' => 'Download the Tuko Kadi App',
                'excerpt' => 'Verify your voter status, stay informed, and access civic tools from your phone.',
                'content' => 'The Tuko Kadi app helps voters verify their information, follow civic updates, and stay connected to voter registration efforts. Add your download links or instructions here from the admin portal.',
                'meta_title' => 'Download App - Tuko Kadi',
                'meta_description' => 'Download the Tuko Kadi app for voter verification, election updates, and civic engagement tools.',
                'cta_label' => 'Join Now',
                'cta_url' => '/?auth=register',
            ],
        ],
        'contact-us' => [
            'label' => 'Contact Us',
            'route' => 'contact.public',
            'defaults' => [
                'title' => 'Contact Us',
                'hero_title' => 'Contact Tuko Kadi',
                'excerpt' => 'Reach the Tuko Kadi team for partnerships, voter mobilization, support, and campaign tools.',
                'content' => "For partnerships, support, voter mobilization, or campaign tools enquiries, contact the Tuko Kadi team. Add phone numbers, email addresses, office locations, and response instructions here from the admin portal.",
                'meta_title' => 'Contact Us - Tuko Kadi',
                'meta_description' => 'Contact Tuko Kadi for support, partnerships, voter mobilization, and campaign tools enquiries.',
                'cta_label' => 'Email Us',
                'cta_url' => 'mailto:info@myleader.co.ke',
            ],
        ],
        'aspirants' => [
            'label' => 'Aspirants Directory SEO',
            'route' => 'aspirants.public',
            'defaults' => [
                'title' => 'Aspirants Directory',
                'hero_title' => '{region} {position} Aspirants',
                'excerpt' => 'Meet the candidates and aspirants seeking to represent {region} in the 2027 Kenya elections.',
                'content' => 'Use {region}, {area}, {position}, and {year} to generate SEO headings and metadata for aspirant listing pages.',
                'meta_title' => '{region} {position} Candidates and Aspirants {year} Kenya Elections',
                'meta_description' => 'Find {region} {position} candidates and aspirants for the {year} Kenya elections. Compare aspirant profiles, regions, parties, and campaign updates.',
                'cta_label' => 'View Aspirants',
                'cta_url' => '/aspirants',
            ],
        ],
    ];

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

    public function updateDonateSettings(array $data): void
    {
        if (isset($data['donation_why_text'])) {
            $this->settingRepository->updateOrCreate('donation_why_text', $data['donation_why_text']);
        }

        if (isset($data['donation_whatsapp_link'])) {
            $this->settingRepository->updateOrCreate('donation_whatsapp_link', $data['donation_whatsapp_link']);
        }
    }

    public function getFrontendPageDefinitions(): array
    {
        return self::FRONTEND_PAGES;
    }

    public function getFrontendPage(string $page): array
    {
        abort_unless(isset(self::FRONTEND_PAGES[$page]), 404);

        $definition = self::FRONTEND_PAGES[$page];
        $stored = $this->settingRepository->firstOrCreate(
            $this->frontendPageKey($page),
            json_encode($definition['defaults'])
        );

        $data = json_decode($stored, true);
        if (! is_array($data)) {
            $data = [];
        }

        return array_merge($definition, [
            'key' => $page,
            'content' => array_merge($definition['defaults'], Arr::only($data, array_keys($definition['defaults']))),
        ]);
    }

    public function updateFrontendPage(string $page, array $data): void
    {
        abort_unless(isset(self::FRONTEND_PAGES[$page]), 404);

        $defaults = self::FRONTEND_PAGES[$page]['defaults'];
        $payload = array_merge($defaults, Arr::only($data, array_keys($defaults)));

        $this->settingRepository->updateOrCreate($this->frontendPageKey($page), json_encode($payload));
    }

    private function frontendPageKey(string $page): string
    {
        return 'frontend_page_' . $page;
    }
}
