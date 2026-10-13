<?php

namespace App\Support;

/**
 * The head snippets an admin switches on from Settings -> Tracking & Ads.
 *
 * Kept free of the database and the container so the normalising and assembly
 * rules can be unit tested on their own.
 */
class TrackingSettings
{
    public const AREA_PUBLIC = 'public';

    public const AREA_ADMIN = 'admin';

    /**
     * Setting keys and their out of the box values.
     *
     * @var array<string, string>
     */
    public const DEFAULTS = [
        'tracking_public' => '1',
        'tracking_admin' => '0',
        'google_analytics_id' => '',
        'google_tag_manager_id' => '',
        'google_adsense_client' => '',
        'google_adsense_auto_ads' => '0',
        'facebook_pixel_id' => '',
        'head_scripts' => '',
    ];

    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    /**
     * @return list<string>
     */
    public static function areas(): array
    {
        return [self::AREA_PUBLIC, self::AREA_ADMIN];
    }

    /**
     * Tidies hand typed IDs into the form the vendors expect, so a clerk can
     * paste whatever the dashboard gave them (with or without the prefix).
     *
     * Checkboxes are read from the input alone: a missing key means unticked.
     *
     * @param  array<string, mixed>  $input
     * @param  array<string, string>  $current
     * @return array<string, string>
     */
    public static function normalize(array $input, array $current = []): array
    {
        $current = array_merge(self::DEFAULTS, $current);

        return [
            'tracking_public' => self::boolean($input['tracking_public'] ?? false) ? '1' : '0',
            'tracking_admin' => self::boolean($input['tracking_admin'] ?? false) ? '1' : '0',
            'google_analytics_id' => self::analyticsId(self::string($input, 'google_analytics_id', $current)),
            'google_tag_manager_id' => self::tagManagerId(self::string($input, 'google_tag_manager_id', $current)),
            'google_adsense_client' => self::adsenseClient(self::string($input, 'google_adsense_client', $current)),
            'google_adsense_auto_ads' => self::boolean($input['google_adsense_auto_ads'] ?? false) ? '1' : '0',
            'facebook_pixel_id' => self::pixelId(self::string($input, 'facebook_pixel_id', $current)),
            'head_scripts' => trim((string) ($input['head_scripts'] ?? $current['head_scripts'])),
        ];
    }

    /**
     * @param  array<string, string>  $settings
     */
    public static function isEnabledFor(array $settings, string $area): bool
    {
        return match ($area) {
            self::AREA_ADMIN => self::boolean($settings['tracking_admin'] ?? false),
            default => self::boolean($settings['tracking_public'] ?? false),
        };
    }

    /**
     * @param  array<string, string>  $settings
     */
    public static function isConfigured(array $settings): bool
    {
        foreach (['google_analytics_id', 'google_tag_manager_id', 'google_adsense_client', 'facebook_pixel_id', 'head_scripts'] as $key) {
            if (trim((string) ($settings[$key] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * The markup for one area, or an empty string when nothing is configured.
     *
     * @param  array<string, string>  $settings
     */
    public static function headMarkup(array $settings): string
    {
        $blocks = [];

        if (($tagManager = trim((string) ($settings['google_tag_manager_id'] ?? ''))) !== '') {
            $blocks[] = self::tagManagerSnippet($tagManager);
        }

        if (($analytics = trim((string) ($settings['google_analytics_id'] ?? ''))) !== '') {
            $blocks[] = self::analyticsSnippet($analytics);
        }

        if (($adsense = trim((string) ($settings['google_adsense_client'] ?? ''))) !== '') {
            $blocks[] = self::adsenseSnippet($adsense, self::boolean($settings['google_adsense_auto_ads'] ?? false));
        }

        if (($pixel = trim((string) ($settings['facebook_pixel_id'] ?? ''))) !== '') {
            $blocks[] = self::pixelSnippet($pixel);
        }

        if (($custom = trim((string) ($settings['head_scripts'] ?? ''))) !== '') {
            $blocks[] = "<!-- Custom head scripts -->\n".$custom;
        }

        if ($blocks === []) {
            return '';
        }

        return "\n".implode("\n", $blocks)."\n";
    }

    private static function analyticsId(string $value): string
    {
        $value = strtoupper(preg_replace('/\s+/', '', $value) ?? '');

        if ($value === '') {
            return '';
        }

        foreach (['G-', 'UA-', 'AW-', 'DC-'] as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return $value;
            }
        }

        return 'G-'.$value;
    }

    private static function tagManagerId(string $value): string
    {
        $value = strtoupper(preg_replace('/\s+/', '', $value) ?? '');

        if ($value === '') {
            return '';
        }

        return str_starts_with($value, 'GTM-') ? $value : 'GTM-'.$value;
    }

    private static function adsenseClient(string $value): string
    {
        $value = strtolower(trim($value));

        if ($value === '') {
            return '';
        }

        if (str_starts_with($value, 'ca-pub-')) {
            return $value;
        }

        return 'ca-pub-'.ltrim($value, '-');
    }

    private static function pixelId(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private static function boolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, string>  $current
     */
    private static function string(array $input, string $key, array $current): string
    {
        return (string) ($input[$key] ?? $current[$key] ?? '');
    }

    private static function tagManagerSnippet(string $id): string
    {
        return sprintf(<<<'HTML'
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','%s');</script>
<!-- End Google Tag Manager -->
HTML, e($id));
    }

    private static function analyticsSnippet(string $id): string
    {
        return sprintf(<<<'HTML'
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=%1$s"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '%1$s');
</script>
HTML, e($id));
    }

    private static function adsenseSnippet(string $client, bool $autoAds): string
    {
        $snippet = sprintf(<<<'HTML'
<meta name="google-adsense-account" content="%1$s">
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=%1$s" crossorigin="anonymous"></script>
HTML, e($client));

        if ($autoAds) {
            $snippet .= sprintf(<<<'HTML'

<script>(adsbygoogle = window.adsbygoogle || []).push({google_ad_client: "%s", enable_page_level_ads: true});</script>
HTML, e($client));
        }

        return "<!-- Google AdSense -->\n".$snippet;
    }

    private static function pixelSnippet(string $id): string
    {
        return sprintf(<<<'HTML'
<!-- Meta Pixel -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '%1$s');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none" alt=""
src="https://www.facebook.com/tr?id=%1$s&amp;ev=PageView&amp;noscript=1"></noscript>
<!-- End Meta Pixel -->
HTML, e($id));
    }
}
