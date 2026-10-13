<?php

namespace Tests\Unit;

use App\Support\TrackingSettings;
use Tests\TestCase;

/**
 * The head snippets are assembled from stored settings, so the normalising and
 * assembly rules are checked here without touching the database.
 */
class TrackingSettingsTest extends TestCase
{
    public function test_ids_are_normalised_however_they_are_pasted(): void
    {
        $values = TrackingSettings::normalize([
            'google_analytics_id' => ' g-abc123xyz4 ',
            'google_tag_manager_id' => 'abc1234',
            'google_adsense_client' => '1234567890123456',
            'facebook_pixel_id' => '123-456-789',
            'head_scripts' => '  <script>console.log(1)</script>  ',
        ]);

        $this->assertSame('G-ABC123XYZ4', $values['google_analytics_id']);
        $this->assertSame('GTM-ABC1234', $values['google_tag_manager_id']);
        $this->assertSame('ca-pub-1234567890123456', $values['google_adsense_client']);
        $this->assertSame('123456789', $values['facebook_pixel_id']);
        $this->assertSame('<script>console.log(1)</script>', $values['head_scripts']);
    }

    public function test_an_existing_prefix_is_kept(): void
    {
        $values = TrackingSettings::normalize([
            'google_analytics_id' => 'UA-123456-1',
            'google_tag_manager_id' => 'GTM-ABC1234',
            'google_adsense_client' => 'ca-pub-1234567890123456',
        ]);

        $this->assertSame('UA-123456-1', $values['google_analytics_id']);
        $this->assertSame('GTM-ABC1234', $values['google_tag_manager_id']);
        $this->assertSame('ca-pub-1234567890123456', $values['google_adsense_client']);
    }

    public function test_blank_input_clears_a_snippet(): void
    {
        $values = TrackingSettings::normalize([
            'google_analytics_id' => '',
            'google_tag_manager_id' => '   ',
        ], [
            'google_analytics_id' => 'G-OLD1234567',
            'google_tag_manager_id' => 'GTM-OLD1234',
        ]);

        $this->assertSame('', $values['google_analytics_id']);
        $this->assertSame('', $values['google_tag_manager_id']);
    }

    public function test_unticked_checkboxes_turn_an_area_off(): void
    {
        $values = TrackingSettings::normalize([], [
            'tracking_public' => '1',
            'tracking_admin' => '1',
            'google_adsense_auto_ads' => '1',
        ]);

        $this->assertSame('0', $values['tracking_public']);
        $this->assertSame('0', $values['tracking_admin']);
        $this->assertSame('0', $values['google_adsense_auto_ads']);

        $on = TrackingSettings::normalize(['tracking_public' => '1', 'tracking_admin' => '1']);

        $this->assertSame('1', $on['tracking_public']);
        $this->assertSame('1', $on['tracking_admin']);
    }

    public function test_areas_are_switched_on_independently(): void
    {
        $publicOnly = TrackingSettings::defaults();

        $this->assertTrue(TrackingSettings::isEnabledFor($publicOnly, TrackingSettings::AREA_PUBLIC));
        $this->assertFalse(TrackingSettings::isEnabledFor($publicOnly, TrackingSettings::AREA_ADMIN));

        $both = TrackingSettings::normalize(['tracking_public' => '1', 'tracking_admin' => '1']);

        $this->assertTrue(TrackingSettings::isEnabledFor($both, TrackingSettings::AREA_PUBLIC));
        $this->assertTrue(TrackingSettings::isEnabledFor($both, TrackingSettings::AREA_ADMIN));
    }

    public function test_nothing_configured_produces_no_markup(): void
    {
        $this->assertSame('', TrackingSettings::headMarkup(TrackingSettings::defaults()));
        $this->assertFalse(TrackingSettings::isConfigured(TrackingSettings::defaults()));
    }

    public function test_every_snippet_is_rendered_in_order(): void
    {
        $markup = TrackingSettings::headMarkup(TrackingSettings::normalize([
            'google_analytics_id' => 'G-ABC123XYZ4',
            'google_tag_manager_id' => 'GTM-ABC1234',
            'google_adsense_client' => 'ca-pub-1234567890123456',
            'google_adsense_auto_ads' => '1',
            'facebook_pixel_id' => '123456789012345',
            'head_scripts' => '<script>window.customTag = 1;</script>',
        ]));

        $this->assertStringContainsString("googletagmanager.com/gtm.js?id='+i+dl", $markup);
        $this->assertStringContainsString("'dataLayer','GTM-ABC1234'", $markup);
        $this->assertStringContainsString('https://www.googletagmanager.com/gtag/js?id=G-ABC123XYZ4', $markup);
        $this->assertStringContainsString("gtag('config', 'G-ABC123XYZ4')", $markup);
        $this->assertStringContainsString('<meta name="google-adsense-account" content="ca-pub-1234567890123456">', $markup);
        $this->assertStringContainsString('adsbygoogle.js?client=ca-pub-1234567890123456', $markup);
        $this->assertStringContainsString('enable_page_level_ads: true', $markup);
        $this->assertStringContainsString("fbq('init', '123456789012345')", $markup);
        $this->assertStringContainsString('https://www.facebook.com/tr?id=123456789012345&amp;ev=PageView&amp;noscript=1', $markup);
        $this->assertStringContainsString('<script>window.customTag = 1;</script>', $markup);

        // Analytics before ads, custom code last.
        $this->assertLessThan(
            strpos($markup, 'google-adsense-account'),
            strpos($markup, 'gtag/js?id=')
        );
        $this->assertGreaterThan(
            strpos($markup, 'facebook.com/tr?id='),
            strpos($markup, 'window.customTag')
        );

        $this->assertTrue(TrackingSettings::isConfigured(TrackingSettings::normalize([
            'google_analytics_id' => 'G-ABC123XYZ4',
        ])));
    }

    public function test_ids_are_escaped_before_they_reach_the_page(): void
    {
        $markup = TrackingSettings::headMarkup([
            'google_analytics_id' => 'G-ABC"><script>alert(1)</script>',
        ]);

        $this->assertStringNotContainsString('"><script>alert(1)</script>', $markup);
        $this->assertStringContainsString('&quot;&gt;&lt;script&gt;', $markup);
    }
}
