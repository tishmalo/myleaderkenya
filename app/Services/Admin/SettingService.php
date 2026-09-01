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
                'content' => 'For partnerships, support, voter mobilization, or campaign tools enquiries, contact the Tuko Kadi team. Add phone numbers, email addresses, office locations, and response instructions here from the admin portal.',
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
        'campaign-tools' => [
            'label' => 'Campaign Tools SEO',
            'route' => 'campaign-tools.public',
            'defaults' => [
                'title' => 'Campaign Tools',
                'hero_title' => 'Campaign Tools for Kenya Elections',
                'excerpt' => 'Explore campaign tools for call centers, bulk SMS, bulk WhatsApp, websites, voter databases, opinion polls, and aspirant profile management.',
                'content' => 'Use this page to manage SEO content for the public campaign tools directory.',
                'meta_title' => 'Campaign Tools for Kenya Election Aspirants - My Leader Kenya',
                'meta_description' => 'Explore campaign tools for Kenya election aspirants, including call centers, bulk SMS, bulk WhatsApp, websites, voter databases, opinion polls, and profile management.',
                'cta_label' => 'View Tools',
                'cta_url' => '/campaign-tools',
            ],
        ],
    ];

    public const NOTIFICATION_EMAILS = [
        'event-ticket' => [
            'label' => 'Event Ticket',
            'description' => 'Sent to attendees after a successful event payment.',
            'placeholders' => ['{attendee_name}', '{event_title}', '{event_date}', '{event_location}', '{amount}'],
            'samples' => [
                '{attendee_name}' => 'Jane Doe',
                '{event_title}' => 'Sample Leadership Assembly',
                '{event_date}' => 'Friday, January 01, 2027 09:00 AM',
                '{event_location}' => 'Nairobi',
                '{amount}' => '1,500',
            ],
            'defaults' => [
                'enabled' => true,
                'subject' => 'Your ticket for {event_title}',
                'body' => '<p>Hi {attendee_name},</p><p>Your registration for <strong>{event_title}</strong> is confirmed.</p><p><strong>Date:</strong> {event_date}<br><strong>Location:</strong> {event_location}</p><p>Find your ticket(s) below and present them at the entrance.</p>',
            ],
        ],
        'password-reset' => [
            'label' => 'Password Reset',
            'description' => 'Sent when a user requests a password reset link.',
            'placeholders' => ['{reset_url}', '{expires_in}'],
            'samples' => [
                '{reset_url}' => 'https://myleader.co.ke/password/reset/sample-token',
                '{expires_in}' => '60',
            ],
            'defaults' => [
                'enabled' => true,
                'subject' => 'Reset your My Leader Kenya password',
                'body' => '<p>Hello,</p><p>You are receiving this email because we received a password reset request for your account.</p><p><a href="{reset_url}">Reset Password</a></p><p>This password reset link will expire in {expires_in} minutes.</p><p>If you did not request a password reset, no further action is required.</p>',
            ],
        ],
        'candidate-claim-link' => [
            'label' => 'Aspirant Claim Link',
            'description' => 'Sent to an aspirant when an admin creates their profile.',
            'placeholders' => ['{candidate_name}', '{claim_url}', '{expires_at}'],
            'samples' => [
                '{candidate_name}' => 'Jane Aspirant',
                '{claim_url}' => 'https://myleader.co.ke/aspirants/claim/sample-token',
                '{expires_at}' => 'Jan 1, 2027 12:00',
            ],
            'defaults' => [
                'enabled' => true,
                'subject' => 'Claim your Tuko Kadi aspirant account',
                'body' => '<p>Hello {candidate_name},</p><p>An admin has created an aspirant profile for you on Tuko Kadi.</p><p>Use the secure link below to set your password and claim your account.</p><p><a href="{claim_url}">Claim Aspirant Account</a></p><p>This link expires on {expires_at}.</p><p>If you did not expect this email, you can ignore it safely.</p>',
            ],
        ],
        'public-pulse-issue' => [
            'label' => 'Public Pulse Session Issue',
            'description' => 'Internal alert when an X session needs attention.',
            'placeholders' => ['{session_label}', '{provider}', '{status}', '{issue}', '{sessions_url}'],
            'samples' => [
                '{session_label}' => '@sample_session',
                '{provider}' => 'twitter',
                '{status}' => 'error',
                '{issue}' => 'Sample issue message',
                '{sessions_url}' => 'https://myleader.co.ke/admin/public-pulse/x-sessions',
            ],
            'defaults' => [
                'enabled' => true,
                'subject' => 'Public Pulse X session needs attention',
                'body' => '<p>Hello,</p><p>A Public Pulse X session needs replacement or review.</p><p><strong>Session:</strong> {session_label}<br><strong>Provider:</strong> {provider}<br><strong>Status:</strong> {status}<br><strong>Issue:</strong> {issue}</p><p><a href="{sessions_url}">Review Public Pulse Sessions</a></p><p>The session has been removed from the active scraper pool until it is healthy again.</p>',
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

    public function getNotificationEmails(): array
    {
        return array_map(
            fn (string $key) => $this->getNotificationEmail($key),
            array_keys(self::NOTIFICATION_EMAILS)
        );
    }

    public function getNotificationEmail(string $key): array
    {
        abort_unless(isset(self::NOTIFICATION_EMAILS[$key]), 404);

        $definition = self::NOTIFICATION_EMAILS[$key];
        $stored = $this->settingRepository->firstOrCreate(
            $this->notificationEmailKey($key),
            json_encode($definition['defaults'])
        );

        $data = json_decode($stored, true);
        $content = array_merge($definition['defaults'], is_array($data) ? $data : []);

        return [
            'key' => $key,
            'label' => $definition['label'],
            'description' => $definition['description'],
            'placeholders' => $definition['placeholders'],
            'samples' => $definition['samples'] ?? [],
            'enabled' => (bool) ($content['enabled'] ?? true),
            'subject' => (string) $content['subject'],
            'body' => (string) $content['body'],
        ];
    }

    public function updateNotificationEmail(string $key, array $data): void
    {
        $current = $this->getNotificationEmail($key);

        $payload = [
            'enabled' => array_key_exists('enabled', $data)
                ? (bool) $data['enabled']
                : (bool) $current['enabled'],
            'subject' => $data['subject'] ?? $current['subject'],
            'body' => $data['body'] ?? $current['body'],
        ];

        $this->settingRepository->updateOrCreate($this->notificationEmailKey($key), json_encode($payload));
    }

    public function notificationTemplate(string $key): ?array
    {
        $email = $this->getNotificationEmail($key);

        if (! $email['enabled']) {
            return null;
        }

        return [
            'subject' => $email['subject'],
            'body' => $email['body'],
        ];
    }

    private function notificationEmailKey(string $key): string
    {
        return 'notification_email_'.$key;
    }

    public function getRecaptchaSettings(): array
    {
        return [
            'recaptchaSiteKey' => $this->settingRepository->firstOrCreate('recaptcha_site_key', ''),
            'recaptchaSecretKey' => $this->settingRepository->firstOrCreate('recaptcha_secret_key', ''),
        ];
    }

    public function updateRecaptchaSettings(array $data): void
    {
        if (array_key_exists('recaptcha_site_key', $data)) {
            $this->settingRepository->updateOrCreate('recaptcha_site_key', trim((string) $data['recaptcha_site_key']));
        }

        if (array_key_exists('recaptcha_secret_key', $data)) {
            $this->settingRepository->updateOrCreate('recaptcha_secret_key', trim((string) $data['recaptcha_secret_key']));
        }
    }

    public function recaptchaSiteKey(): string
    {
        return $this->settingRepository->firstOrCreate('recaptcha_site_key', '');
    }

    public function recaptchaSecretKey(): string
    {
        return $this->settingRepository->firstOrCreate('recaptcha_secret_key', '');
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
        return 'frontend_page_'.$page;
    }
}
