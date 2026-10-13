<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\Web\TrackingScriptService;
use App\Support\TrackingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The admin page that manages the head tracking code, and the fact that what is
 * saved there actually reaches the pages.
 */
class TrackingSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);
    }

    private function clerk(): User
    {
        return User::factory()->create([
            'name' => 'Clerk Kim',
            'username' => 'clerk-kim',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::ADMIN),
        ]);
    }

    public function test_the_page_shows_the_current_settings(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.tracking'))
            ->assertOk()
            ->assertSee('Tracking & Ads')
            ->assertSee('Google Analytics measurement ID')
            ->assertSee('Google Tag Manager container ID')
            ->assertSee('Google AdSense publisher ID')
            ->assertSee('Meta (Facebook) pixel ID')
            ->assertSee('Any other head code')
            ->assertSee('Public website')
            ->assertSee('Admin & aspirant dashboards');
    }

    public function test_settings_can_be_saved_and_are_normalised(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.tracking.update'), [
                'tracking_public' => '1',
                'tracking_admin' => '1',
                'google_analytics_id' => 'abc123xyz4',
                'google_tag_manager_id' => 'GTM-ABC1234',
                'google_adsense_client' => '1234567890123456',
                'google_adsense_auto_ads' => '1',
                'facebook_pixel_id' => '123456789012345',
                'head_scripts' => '<script>window.mlk = 1;</script>',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', ['key' => 'google_analytics_id', 'value' => 'G-ABC123XYZ4']);
        $this->assertDatabaseHas('settings', ['key' => 'google_tag_manager_id', 'value' => 'GTM-ABC1234']);
        $this->assertDatabaseHas('settings', ['key' => 'google_adsense_client', 'value' => 'ca-pub-1234567890123456']);
        $this->assertDatabaseHas('settings', ['key' => 'facebook_pixel_id', 'value' => '123456789012345']);
        $this->assertDatabaseHas('settings', ['key' => 'tracking_admin', 'value' => '1']);
    }

    public function test_a_bad_id_is_rejected(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.tracking.update'), [
                'google_analytics_id' => 'not an id!',
                'google_adsense_client' => 'pub-123',
                'facebook_pixel_id' => 'abc',
            ])
            ->assertSessionHasErrors(['google_analytics_id', 'google_adsense_client', 'facebook_pixel_id']);

        $this->assertDatabaseMissing('settings', ['key' => 'google_analytics_id']);
    }

    public function test_the_snippets_reach_the_public_site_head(): void
    {
        $this->actingAs($this->superAdmin())->post(route('admin.tracking.update'), [
            'tracking_public' => '1',
            'google_analytics_id' => 'G-ABC123XYZ4',
            'google_adsense_client' => 'ca-pub-1234567890123456',
        ]);

        $response = $this->get(route('landing'))->assertOk();

        $response->assertSee('gtag/js?id=G-ABC123XYZ4', false);
        $response->assertSee('adsbygoogle.js?client=ca-pub-1234567890123456', false);
    }

    public function test_dashboards_are_off_until_switched_on(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.tracking.update'), [
            'tracking_public' => '1',
            'google_analytics_id' => 'G-ABC123XYZ4',
        ]);

        // Off by default, so the admin dashboard stays clean.
        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('gtag/js?id=G-ABC123XYZ4', false);

        // ...and the public site still carries it.
        $this->get(route('landing'))->assertOk()->assertSee('gtag/js?id=G-ABC123XYZ4', false);

        $this->actingAs($admin)->post(route('admin.tracking.update'), [
            'tracking_public' => '1',
            'tracking_admin' => '1',
            'google_analytics_id' => 'G-ABC123XYZ4',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('gtag/js?id=G-ABC123XYZ4', false);
    }

    public function test_turning_the_public_side_off_removes_the_code(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.tracking.update'), [
            'tracking_public' => '1',
            'google_analytics_id' => 'G-ABC123XYZ4',
        ]);

        $this->get(route('landing'))->assertOk()->assertSee('gtag/js?id=G-ABC123XYZ4', false);

        // The checkbox is absent when unticked.
        $this->actingAs($admin)->post(route('admin.tracking.update'), [
            'google_analytics_id' => 'G-ABC123XYZ4',
        ]);

        $this->get(route('landing'))->assertOk()->assertDontSee('gtag/js?id=G-ABC123XYZ4', false);
    }

    public function test_custom_head_code_is_rendered_verbatim(): void
    {
        $this->actingAs($this->superAdmin())->post(route('admin.tracking.update'), [
            'tracking_public' => '1',
            'head_scripts' => '<meta name="verify-me" content="abc123">',
        ]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('<meta name="verify-me" content="abc123">', false);
    }

    public function test_the_cached_markup_is_refreshed_when_settings_change(): void
    {
        $tracking = app(TrackingScriptService::class);

        // Warm the cache with the default (nothing configured) state.
        $this->assertSame('', $tracking->headMarkup());

        $this->actingAs($this->superAdmin())->post(route('admin.tracking.update'), [
            'tracking_public' => '1',
            'google_analytics_id' => 'G-FRESH12345',
        ]);

        $this->assertStringContainsString('G-FRESH12345', $tracking->headMarkup());
    }

    public function test_only_super_admins_can_manage_tracking(): void
    {
        $clerk = $this->clerk();

        $this->actingAs($clerk)->get(route('admin.tracking'))->assertForbidden();

        $this->actingAs($clerk)
            ->post(route('admin.tracking.update'), ['google_analytics_id' => 'G-ABC123XYZ4'])
            ->assertForbidden();

        $this->assertDatabaseMissing('settings', ['key' => 'google_analytics_id']);
        $this->assertSame('', Cache::get('tracking:head:public', ''));
    }

    public function test_the_missing_settings_row_falls_back_to_the_defaults(): void
    {
        $settings = app(TrackingScriptService::class)->settings();

        $this->assertSame(TrackingSettings::DEFAULTS, $settings);
    }
}
