<?php

namespace Tests\Feature;

use App\Models\CampaignTool;
use App\Models\CampaignToolRequest;
use App\Models\Setting;
use App\Models\SpamIpOverride;
use App\Models\SpamRule;
use App\Models\SpamSample;
use App\Services\Web\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CampaignToolFeatureRequestSpamTest extends TestCase
{
    use RefreshDatabase;

    private CampaignTool $tool;

    protected function setUp(): void
    {
        parent::setUp();

        // IP lookup requires outbound HTTP; disabled by default so existing tests
        // treat every request as allowed. Individual IP tests re-enable it.
        config(['spam_filter.ip_lookup.enabled' => false]);

        Cache::flush();

        $this->tool = CampaignTool::create([
            'title' => 'Bulk SMS',
            'slug' => 'bulk-sms',
            'content' => 'Bulk SMS tool content.',
            'status' => 'published',
            'sort_order' => 1,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'requester_name' => 'Jane Aspirant',
            'email' => 'jane@example.com',
            'phone' => '+254700000000',
            'requested_feature' => 'Add delivery receipts',
            'use_case' => 'We want to track message delivery.',
            '_load_time' => (string) (now()->getPreciseTimestamp(3) - 10_000),
        ], $overrides);
    }

    public function test_valid_submission_is_accepted_when_recaptcha_is_disabled(): void
    {
        $response = $this->post(route('campaign-tools.requests.store', $this->tool), $this->validPayload());

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('campaign_tool_requests', [
            'campaign_tool_id' => $this->tool->id,
            'email' => 'jane@example.com',
            'request_type' => 'feature',
            'is_spam' => false,
        ]);
    }

    public function test_submission_is_rejected_when_recaptcha_configured_but_token_missing(): void
    {
        Setting::create(['key' => 'recaptcha_site_key', 'value' => 'site-key']);
        Setting::create(['key' => 'recaptcha_secret_key', 'value' => 'secret-key']);

        $response = $this->post(route('campaign-tools.requests.store', $this->tool), $this->validPayload());

        $response->assertSessionHasErrors('g-recaptcha-response');
        $this->assertDatabaseCount('campaign_tool_requests', 0);
    }

    public function test_submission_is_accepted_when_recaptcha_token_is_valid(): void
    {
        Setting::create(['key' => 'recaptcha_site_key', 'value' => 'site-key']);
        Setting::create(['key' => 'recaptcha_secret_key', 'value' => 'secret-key']);

        $this->mock(RecaptchaService::class, function ($mock): void {
            $mock->shouldReceive('enabled')->andReturn(true);
            $mock->shouldReceive('verify')->andReturn(true);
        });

        $response = $this->post(
            route('campaign-tools.requests.store', $this->tool),
            $this->validPayload(['g-recaptcha-response' => 'valid-token'])
        );

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('campaign_tool_requests', ['email' => 'jane@example.com', 'is_spam' => false]);
    }

    public function test_submission_is_rejected_when_honeypot_is_filled(): void
    {
        $response = $this->post(
            route('campaign-tools.requests.store', $this->tool),
            $this->validPayload(['company_website' => 'https://spam.example.com'])
        );

        $response->assertSessionHasErrors('company_website');
        $this->assertDatabaseCount('campaign_tool_requests', 0);
    }

    public function test_submission_is_rejected_when_form_was_submitted_too_fast(): void
    {
        $response = $this->post(
            route('campaign-tools.requests.store', $this->tool),
            $this->validPayload(['_load_time' => (string) now()->getPreciseTimestamp(3)])
        );

        $response->assertSessionHasErrors('_load_time');
        $this->assertDatabaseCount('campaign_tool_requests', 0);
    }

    public function test_duplicate_pending_request_is_quarantined_as_spam(): void
    {
        CampaignToolRequest::create([
            'campaign_tool_id' => $this->tool->id,
            'request_type' => 'feature',
            'tool_title' => $this->tool->title,
            'requester_name' => 'Jane Aspirant',
            'email' => 'jane@example.com',
            'requested_feature' => 'Add delivery receipts',
            'status' => 'new',
            'is_spam' => false,
        ]);

        $response = $this->post(route('campaign-tools.requests.store', $this->tool), $this->validPayload());

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('campaign_tool_requests', [
            'email' => 'jane@example.com',
            'is_spam' => true,
            'spam_reason' => 'duplicate_pending_request',
        ]);
    }

    public function test_keyword_spam_is_hard_rejected_and_recorded(): void
    {
        $response = $this->post(
            route('campaign-tools.requests.store', $this->tool),
            $this->validPayload(['use_case' => 'Get a pay day cash advance today with quick approval.'])
        );

        $response->assertSessionHasErrors('requested_feature');
        $this->assertDatabaseCount('campaign_tool_requests', 0);
        $this->assertDatabaseHas('spam_samples', ['reason' => 'blocked_keyword']);
    }

    public function test_url_spam_is_hard_rejected_and_recorded(): void
    {
        $response = $this->post(
            route('campaign-tools.requests.store', $this->tool),
            $this->validPayload(['use_case' => 'Check out my site at https://vietnam.everycalculators.com for more info.'])
        );

        $response->assertSessionHasErrors('requested_feature');
        $this->assertDatabaseCount('campaign_tool_requests', 0);
        $this->assertDatabaseHas('spam_samples', ['reason' => 'url_in_message']);
    }

    public function test_html_spam_is_hard_rejected_and_recorded(): void
    {
        $response = $this->post(
            route('campaign-tools.requests.store', $this->tool),
            $this->validPayload(['use_case' => 'Getting an <a href=https://novvaloans.com/>pay day cash advance</a> is easy.'])
        );

        $response->assertSessionHasErrors('requested_feature');
        $this->assertDatabaseCount('campaign_tool_requests', 0);
        $this->assertDatabaseHas('spam_samples', ['reason' => 'html_in_message']);
    }

    public function test_suspicious_email_and_phone_are_rejected(): void
    {
        $response = $this->post(
            route('campaign-tools.requests.store', $this->tool),
            $this->validPayload([
                'email' => '1071@farironalds.com',
                'phone' => '81149842423',
                'use_case' => 'Send me loan details please.',
            ])
        );

        $response->assertSessionHasErrors('requested_feature');
        $this->assertDatabaseCount('campaign_tool_requests', 0);
        $this->assertDatabaseHas('spam_samples', ['reason' => 'suspicious_email']);
    }

    public function test_character_limit_is_enforced_on_the_server(): void
    {
        $response = $this->post(
            route('campaign-tools.requests.store', $this->tool),
            $this->validPayload(['use_case' => str_repeat('a', 2001)])
        );

        $response->assertSessionHasErrors('use_case');
        $this->assertDatabaseCount('campaign_tool_requests', 0);
    }

    public function test_foreign_ip_request_is_quarantined_as_spam(): void
    {
        config(['spam_filter.ip_lookup.enabled' => true]);

        Http::fake(['ip-api.com/*' => Http::response(['status' => 'success', 'countryCode' => 'US'])]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->post(route('campaign-tools.requests.store', $this->tool), $this->validPayload());

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('campaign_tool_requests', [
            'email' => 'jane@example.com',
            'is_spam' => true,
            'spam_reason' => 'non_kenyan_ip',
        ]);
    }

    public function test_kenyan_ip_request_is_accepted(): void
    {
        config(['spam_filter.ip_lookup.enabled' => true]);

        Http::fake(['ip-api.com/*' => Http::response(['status' => 'success', 'countryCode' => 'KE'])]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '105.160.1.1'])
            ->post(route('campaign-tools.requests.store', $this->tool), $this->validPayload());

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('campaign_tool_requests', [
            'email' => 'jane@example.com',
            'is_spam' => false,
        ]);
    }

    public function test_purge_command_removes_old_spam_but_keeps_fresh(): void
    {
        $oldRequest = CampaignToolRequest::create([
            'campaign_tool_id' => $this->tool->id,
            'request_type' => 'feature',
            'tool_title' => $this->tool->title,
            'requester_name' => 'Old Spam',
            'email' => 'old@spam.com',
            'requested_feature' => 'old',
            'status' => 'new',
            'is_spam' => true,
            'spam_reason' => 'non_kenyan_ip',
        ]);
        $oldRequest->forceFill(['created_at' => now()->subDays(40)])->save();

        $oldSample = SpamSample::create([
            'payload' => ['use_case' => 'old sample'],
            'text_hash' => sha1('old sample'),
            'reason' => 'blocked_keyword',
        ]);
        $oldSample->forceFill(['created_at' => now()->subDays(40)])->save();

        SpamSample::create([
            'payload' => ['use_case' => 'fresh sample'],
            'text_hash' => sha1('fresh sample'),
            'reason' => 'blocked_keyword',
            'created_at' => now()->subHour(),
        ]);

        $this->artisan('campaign-tools:purge-spam')->assertSuccessful();

        $this->assertDatabaseCount('campaign_tool_requests', 0);
        $this->assertDatabaseMissing('spam_samples', ['text_hash' => sha1('old sample')]);
        $this->assertDatabaseHas('spam_samples', ['text_hash' => sha1('fresh sample')]);
    }

    public function test_bot_verify_clears_a_blocked_ip_after_recaptcha(): void
    {
        Setting::create(['key' => 'recaptcha_site_key', 'value' => 'site-key']);
        Setting::create(['key' => 'recaptcha_secret_key', 'value' => 'secret-key']);

        SpamRule::create(['type' => 'ip', 'value' => '203.0.113.5', 'enabled' => true, 'source' => 'admin']);

        $this->mock(RecaptchaService::class, function ($mock): void {
            $mock->shouldReceive('enabled')->andReturn(true);
            $mock->shouldReceive('verify')->andReturn(true);
        });

        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.5'])
            ->post(route('bot.verify'), ['g-recaptcha-response' => 'valid-token']);

        $response->assertRedirect();

        $this->assertDatabaseMissing('spam_rules', ['type' => 'ip', 'value' => '203.0.113.5']);
        $this->assertDatabaseHas('spam_ip_overrides', ['ip' => '203.0.113.5', 'action' => 'allow']);
    }
}
