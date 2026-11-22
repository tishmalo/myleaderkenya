<?php

namespace Tests\Feature;

use App\Jobs\SendCandidateBulkSms;
use App\Models\AspirantPoll;
use App\Models\Candidate;
use App\Models\CandidateCampaignToolEntitlement;
use App\Models\CandidateSmsSetting;
use App\Models\CandidateSupportContact;
use App\Models\CandidateTokenWallet;
use App\Models\CampaignTool;
use App\Models\Position;
use App\Models\SupportGroupType;
use App\Models\User;
use App\Services\Web\AspirantTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CampaignToolApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['spam_filter.ip_lookup.enabled' => false]);

        Cache::flush();
    }

    public function test_the_public_directory_lists_only_published_tools(): void
    {
        $this->publishedTool('Bulk SMS', 'bulk-sms');
        $this->publishedTool('Call Center', 'call-center');
        $this->tool('Hidden Tool', 'hidden-tool', 'draft');

        $this->getJson('/api/campaign-tools')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['title' => 'Bulk SMS'])
            ->assertJsonFragment(['title' => 'Call Center'])
            ->assertJsonMissing(['title' => 'Hidden Tool']);
    }

    public function test_the_directory_rejects_an_out_of_range_page_size(): void
    {
        $this->getJson('/api/campaign-tools?per_page=500')
            ->assertStatus(422)
            ->assertJsonValidationErrors('per_page');
    }

    public function test_a_published_tool_can_be_viewed_but_a_draft_tool_is_hidden(): void
    {
        $published = $this->publishedTool('Bulk SMS', 'bulk-sms');
        $draft = $this->tool('Hidden Tool', 'hidden-tool', 'draft');

        $this->getJson('/api/campaign-tools/' . $published->slug)
            ->assertOk()
            ->assertJsonPath('data.slug', 'bulk-sms')
            ->assertJsonPath('data.content', 'Bulk SMS tool description.');

        $this->getJson('/api/campaign-tools/' . $draft->slug)->assertNotFound();
    }

    public function test_a_feature_request_is_stored_and_links_the_signed_in_aspirant(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Bulk SMS', 'bulk-sms');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/campaign-tools/' . $tool->slug . '/requests', [
                'requester_name' => 'Jane Aspirant',
                'email' => 'jane@example.com',
                'requested_feature' => 'Add delivery receipts',
                'use_case' => 'We want to track message delivery.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.request_type', 'feature')
            ->assertJsonPath('data.campaign_tool_id', $tool->id);

        $this->assertDatabaseHas('campaign_tool_requests', [
            'campaign_tool_id' => $tool->id,
            'user_id' => $user->id,
            'candidate_id' => $candidate->id,
            'request_type' => 'feature',
            'status' => 'new',
        ]);
    }

    public function test_a_feature_request_needs_an_email_or_phone(): void
    {
        $tool = $this->publishedTool('Bulk SMS', 'bulk-sms');

        $this->postJson('/api/campaign-tools/' . $tool->slug . '/requests', [
            'requester_name' => 'Jane Aspirant',
            'requested_feature' => 'Add delivery receipts',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');

        $this->assertDatabaseCount('campaign_tool_requests', 0);
    }

    public function test_a_feature_request_for_a_draft_tool_is_not_found(): void
    {
        $tool = $this->tool('Hidden Tool', 'hidden-tool', 'draft');

        $this->postJson('/api/campaign-tools/' . $tool->slug . '/requests', [
            'requester_name' => 'Jane Aspirant',
            'email' => 'jane@example.com',
            'requested_feature' => 'Add delivery receipts',
        ])->assertNotFound();
    }

    public function test_an_unknown_aspirant_tool_key_is_not_found(): void
    {
        $user = $this->aspirantUser();
        $this->candidateFor($user);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/aspirant/campaign-tools/not-a-tool')
            ->assertNotFound();
    }

    public function test_an_account_without_a_candidate_gets_not_found(): void
    {
        $this->publishedTool('Support Groups', 'support-groups');

        $this->actingAs($this->aspirantUser(), 'sanctum')
            ->getJson('/api/aspirant/campaign-tools/support-groups')
            ->assertNotFound()
            ->assertJsonPath('message', 'No aspirant profile is linked to this account yet.');
    }

    public function test_an_unentitled_tool_returns_forbidden_with_the_reason(): void
    {
        $user = $this->aspirantUser();
        $this->candidateFor($user);
        $this->publishedTool('Opinion Polls', 'opinion-polls');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/aspirant/campaign-tools/opinion-polls')
            ->assertForbidden()
            ->assertJsonPath('message', 'Choose a package and wait for admin activation.');
    }

    public function test_the_tool_payload_includes_scope_wallet_and_support_data(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Support Groups', 'support-groups');
        $this->entitlement($candidate, $tool, 'support-groups');

        $groupType = $this->groupType();
        $this->supportContact($candidate, $groupType, 'Mary Wambui');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/aspirant/campaign-tools/support-groups')
            ->assertOk()
            ->assertJsonPath('data.key', 'support-groups')
            ->assertJsonPath('data.scope.type', 'county')
            ->assertJsonPath('data.scope.value', 'Nairobi')
            ->assertJsonPath('data.availability.available', true)
            ->assertJsonPath('data.tool.slug', 'support-groups')
            ->assertJsonPath('data.wallet.candidate_id', $candidate->id)
            ->assertJsonFragment(['slug' => 'volunteers'])
            ->assertJsonPath('data.support_contacts.0.name', 'Mary Wambui');
    }

    public function test_a_poll_can_be_created_with_an_options_array(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Opinion Polls', 'opinion-polls');
        $this->entitlement($candidate, $tool, 'opinion-polls');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/aspirant/campaign-tools/opinion-polls/polls', [
                'question' => 'Which project matters most?',
                'options' => ['Roads', 'Water'],
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Poll draft saved.');

        $poll = AspirantPoll::query()->firstOrFail();

        $this->assertSame(['Roads', 'Water'], $poll->options);
        $this->assertSame('draft', $poll->status);
        $this->assertSame('county', $poll->scope_type);
        $this->assertSame('Nairobi', $poll->scope_value);
        $this->assertSame(4, CandidateCampaignToolEntitlement::firstOrFail()->remaining_allowance);
    }

    public function test_a_poll_needs_at_least_two_options(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Opinion Polls', 'opinion-polls');
        $this->entitlement($candidate, $tool, 'opinion-polls');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/aspirant/campaign-tools/opinion-polls/polls', [
                'question' => 'Only one choice?',
                'options' => ['Yes'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('options');
    }

    public function test_a_call_log_is_only_recorded_for_a_scoped_voter(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Call Center', 'call-center');
        $this->entitlement($candidate, $tool, 'call-center', 10);

        $voter = $this->voter();
        $outsider = $this->voter(['county' => 'Mombasa']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/aspirant/campaign-tools/call-center/calls', [
                'voter_user_id' => $voter->id,
                'outcome' => 'supporter',
                'notes' => 'Will volunteer next week.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.voter_user_id', $voter->id);

        $this->assertDatabaseHas('candidate_call_logs', [
            'candidate_id' => $candidate->id,
            'voter_user_id' => $voter->id,
            'outcome' => 'supporter',
            'scope_type' => 'county',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/aspirant/campaign-tools/call-center/calls', [
                'voter_user_id' => $outsider->id,
                'outcome' => 'reached',
            ])
            ->assertNotFound();
    }

    public function test_a_call_script_is_saved_for_the_candidate_scope(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Call Center', 'call-center');
        $this->entitlement($candidate, $tool, 'call-center');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/aspirant/campaign-tools/call-center/script', [
                'script' => 'Good evening, I am calling on behalf of your aspirant to hear your priorities.',
                'callback_priority' => 'supporters',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Call script saved.');

        $this->assertDatabaseHas('candidate_call_scripts', [
            'candidate_id' => $candidate->id,
            'callback_priority' => 'supporters',
            'scope_type' => 'county',
            'scope_value' => 'Nairobi',
        ]);
    }

    public function test_a_campaign_website_request_is_submitted(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Campaign Website', 'campaign-website');
        $this->entitlement($candidate, $tool, 'campaign-website');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/aspirant/campaign-tools/campaign-website/request', [
                'candidate_name' => 'Aspirant Owner',
                'website_type' => 'standard',
                'email' => 'owner@example.com',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Campaign website request submitted.')
            ->assertJsonPath('data.status', 'new');

        $this->assertDatabaseHas('campaign_website_requests', [
            'candidate_id' => $candidate->id,
            'user_id' => $user->id,
            'website_type' => 'standard',
            'status' => 'new',
        ]);
    }

    public function test_support_contacts_are_listed_created_updated_and_deleted(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Support Groups', 'support-groups');
        $this->entitlement($candidate, $tool, 'support-groups', 10);
        $groupType = $this->groupType();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/aspirant/support-groups/contacts', [
                'support_group_type_id' => $groupType->id,
                'name' => 'Mary Wambui',
                'phone' => '0722000111',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Mary Wambui')
            ->assertJsonPath('data.support_group_type.slug', 'volunteers');

        $contact = CandidateSupportContact::query()->firstOrFail();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/aspirant/support-groups/contacts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Mary Wambui');

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/aspirant/support-groups/contacts/' . $contact->id, [
                'support_group_type_id' => $groupType->id,
                'name' => 'Mary W. Kariuki',
                'email' => 'mary@example.com',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Mary W. Kariuki');

        $this->assertDatabaseHas('candidate_support_contacts', [
            'id' => $contact->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/aspirant/support-groups/contacts/' . $contact->id)
            ->assertOk();

        $this->assertDatabaseCount('candidate_support_contacts', 0);
    }

    public function test_a_candidate_cannot_touch_another_candidates_support_contact(): void
    {
        $owner = $this->aspirantUser();
        $ownerCandidate = $this->candidateFor($owner);
        $groupType = $this->groupType();
        $contact = $this->supportContact($ownerCandidate, $groupType, 'Owner Contact');

        $intruder = $this->aspirantUser();
        $this->candidateFor($intruder);

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson('/api/aspirant/support-groups/contacts/' . $contact->id)
            ->assertForbidden();

        $this->assertDatabaseHas('candidate_support_contacts', ['id' => $contact->id]);
    }

    public function test_the_support_group_types_endpoint_lists_active_types(): void
    {
        $this->groupType();
        SupportGroupType::create(['name' => 'Hidden', 'slug' => 'hidden', 'is_active' => false, 'sort_order' => 2]);

        $this->actingAs($this->aspirantUser(), 'sanctum')
            ->getJson('/api/aspirant/support-groups/types')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'volunteers'])
            ->assertJsonMissing(['slug' => 'hidden']);
    }

    public function test_bulk_sms_reserves_tokens_and_queues_the_message(): void
    {
        Queue::fake();

        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $this->publishedTool('Bulk SMS', 'bulk-sms');
        $this->smsSetting($candidate);
        $this->voter();

        // Grants the initial aspirant tokens that the SMS send reserves.
        app(AspirantTokenService::class)->walletForCandidate($candidate);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/aspirant/campaign-tools/bulk-sms/send', [
                'message' => 'Join us at the rally on Saturday.',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Bulk SMS queued.')
            ->assertJsonPath('data.status', 'queued')
            ->assertJsonPath('data.recipient_count', 1)
            ->assertJsonPath('data.privacy_acknowledged_at', null);

        $this->assertDatabaseHas('candidate_sms_messages', [
            'candidate_id' => $candidate->id,
            'status' => 'queued',
            'recipient_source' => 'registered_voters',
            'recipient_count' => 1,
        ]);

        $this->assertSame(19, CandidateTokenWallet::where('candidate_id', $candidate->id)->firstOrFail()->balance);

        Queue::assertPushed(SendCandidateBulkSms::class);
    }

    public function test_bulk_sms_without_reachable_voters_returns_a_warning(): void
    {
        Queue::fake();

        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $this->publishedTool('Bulk SMS', 'bulk-sms');
        $this->smsSetting($candidate);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/aspirant/campaign-tools/bulk-sms/send', [
                'message' => 'Join us at the rally on Saturday.',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'No valid phone numbers were found for your scoped voters.');

        $this->assertDatabaseCount('candidate_sms_messages', 0);
        Queue::assertNothingPushed();
    }

    private function aspirantUser(): User
    {
        return User::factory()->create([
            'is_aspirant' => true,
            'is_voter' => false,
            'gender' => 'female',
            'year_of_birth' => 1990,
            'county' => 'Nairobi',
            'constituency' => 'Westlands',
            'ward' => 'Kitisuru',
            'country_of_residence' => 'Kenya',
        ]);
    }

    private function candidateFor(User $user, string $position = 'Governor'): Candidate
    {
        return Candidate::create([
            'name' => 'Aspirant ' . $user->id,
            'user_id' => $user->id,
            'position_id' => Position::firstOrCreate(['name' => $position])->id,
            'county' => 'Nairobi',
            'constituency' => 'Westlands',
            'ward' => 'Kitisuru',
            'approval_status' => 'approved',
        ]);
    }

    private function voter(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'is_voter' => true,
            'is_registered' => true,
            'county' => 'Nairobi',
            'phone' => '0712345678',
        ], $overrides));
    }

    private function tool(string $title, string $slug, string $status): CampaignTool
    {
        return CampaignTool::create([
            'title' => $title,
            'slug' => $slug,
            'nav_label' => $title,
            'excerpt' => $title,
            'content' => $title . ' tool description.',
            'status' => $status,
        ]);
    }

    private function publishedTool(string $title, string $slug): CampaignTool
    {
        return $this->tool($title, $slug, 'published');
    }

    private function entitlement(Candidate $candidate, CampaignTool $tool, string $key, int $remaining = 5): void
    {
        CandidateCampaignToolEntitlement::create([
            'candidate_id' => $candidate->id,
            'campaign_tool_id' => $tool->id,
            'tool_key' => $key,
            'entitlement_type' => 'quantity',
            'allowance' => 5,
            'remaining_allowance' => $remaining,
            'status' => 'active',
            'activated_at' => now(),
        ]);
    }

    private function smsSetting(Candidate $candidate): void
    {
        CandidateSmsSetting::create([
            'candidate_id' => $candidate->id,
            'enabled' => true,
            'provider' => 'infobip',
            'base_url' => 'https://api.infobip.com',
            'username' => 'test-user',
            'password' => 'test-password',
            'sender_name' => 'MYLEADER',
        ]);
    }

    private function groupType(string $name = 'Volunteers'): SupportGroupType
    {
        return SupportGroupType::create([
            'name' => $name,
            'slug' => strtolower($name),
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function supportContact(Candidate $candidate, SupportGroupType $groupType, string $name): CandidateSupportContact
    {
        return CandidateSupportContact::create([
            'candidate_id' => $candidate->id,
            'support_group_type_id' => $groupType->id,
            'name' => $name,
            'phone' => '0722000111',
            'created_by' => $candidate->user_id,
            'updated_by' => $candidate->user_id,
        ]);
    }
}
