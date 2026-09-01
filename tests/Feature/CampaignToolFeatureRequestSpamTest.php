<?php

namespace Tests\Feature;

use App\Models\CampaignTool;
use App\Models\CampaignToolRequest;
use App\Models\Setting;
use App\Services\Web\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignToolFeatureRequestSpamTest extends TestCase
{
    use RefreshDatabase;

    private CampaignTool $tool;

    protected function setUp(): void
    {
        parent::setUp();

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
}
