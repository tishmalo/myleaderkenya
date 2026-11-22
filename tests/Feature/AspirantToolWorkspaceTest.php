<?php

namespace Tests\Feature;

use App\Jobs\SendCandidateBulkSms;
use App\Models\Candidate;
use App\Models\CandidateCampaignToolEntitlement;
use App\Models\CandidateSmsSetting;
use App\Models\CandidateSupportContact;
use App\Models\CandidateTokenWallet;
use App\Models\CampaignTool;
use App\Models\CampaignWebsiteSample;
use App\Models\Position;
use App\Models\SupportGroupType;
use App\Models\User;
use App\Services\Sms\InfobipSmsService;
use App\Services\Web\AspirantTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AspirantToolWorkspaceTest extends TestCase
{
    use RefreshDatabase;

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

    private function publishedTool(string $title, string $key): CampaignTool
    {
        return CampaignTool::create([
            'title' => $title,
            'slug' => $key,
            'nav_label' => $title,
            'excerpt' => $title,
            'content' => $title . ' tool description.',
            'status' => 'published',
        ]);
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

    public function test_an_unknown_tool_key_returns_not_found(): void
    {
        $this->actingAs($this->aspirantUser())
            ->get(route('aspirant.tools.show', 'not-a-tool'))
            ->assertNotFound();
    }

    public function test_an_unpublished_tool_redirects_to_the_dashboard(): void
    {
        $user = $this->aspirantUser();
        $this->candidateFor($user);

        $this->actingAs($user)
            ->get(route('aspirant.tools.show', 'bulk-sms'))
            ->assertRedirect(route('aspirant.dashboard'))
            ->assertSessionHas('warning', 'That campaign tool is not enabled yet. Ask an admin to publish it first.');
    }

    public function test_the_bulk_sms_page_renders_for_a_candidate_with_provider_credentials(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $this->publishedTool('Bulk SMS', 'bulk-sms');
        $this->smsSetting($candidate);

        $this->mock(InfobipSmsService::class, function ($mock): void {
            $mock->shouldReceive('accountBalance')->once()->andReturn([
                'available' => true,
                'amount' => 500,
                'currency' => 'KES',
                'formatted' => 'KES 500',
                'raw' => [],
                'error' => null,
            ]);
        });

        $this->actingAs($user)
            ->get(route('aspirant.tools.show', 'bulk-sms'))
            ->assertOk()
            ->assertSee('Bulk SMS')
            ->assertSee('KES 500');
    }

    public function test_publishing_an_opinion_poll_broadcasts_it_to_the_scoped_group(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Opinion Polls', 'opinion-polls');
        $this->entitlement($candidate, $tool, 'opinion-polls');
        $voter = $this->voter();

        $this->actingAs($user)
            ->post(route('aspirant.tools.polls.store'), [
                'question' => 'Should the county fund more clinics?',
                'options' => "Yes\nNo",
                'status' => 'published',
            ])
            ->assertRedirect(route('aspirant.tools.show', 'opinion-polls'))
            ->assertSessionHas('success', 'Poll published to the Nairobi County chat group.');

        $poll = \App\Models\AspirantPoll::query()->firstOrFail();

        $this->assertSame('published', $poll->status);
        $this->assertSame(['Yes', 'No'], $poll->options);
        $this->assertSame('county', $poll->scope_type);
        $this->assertSame('Nairobi', $poll->scope_value);

        $this->assertDatabaseHas('groups', ['id' => $poll->group_id, 'name' => 'Nairobi County Opinion Polls']);
        $this->assertDatabaseHas('group_members', ['group_id' => $poll->group_id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('group_members', ['group_id' => $poll->group_id, 'user_id' => $voter->id]);
        $this->assertDatabaseHas('group_messages', [
            'group_id' => $poll->group_id,
            'message_type' => 'poll',
            'aspirant_poll_id' => $poll->id,
        ]);

        $this->assertSame(4, CandidateCampaignToolEntitlement::firstOrFail()->remaining_allowance);
    }

    public function test_a_poll_draft_does_not_create_a_chat_group(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Opinion Polls', 'opinion-polls');
        $this->entitlement($candidate, $tool, 'opinion-polls');

        $this->actingAs($user)
            ->post(route('aspirant.tools.polls.store'), [
                'question' => 'Which project matters most?',
                'options' => "Roads\nWater",
            ])
            ->assertRedirect(route('aspirant.tools.show', 'opinion-polls'))
            ->assertSessionHas('success', 'Poll draft saved. Publish it when you are ready to send it to voters.');

        $poll = \App\Models\AspirantPoll::query()->firstOrFail();

        $this->assertSame('draft', $poll->status);
        $this->assertNull($poll->group_id);
        $this->assertDatabaseCount('groups', 0);
    }

    public function test_a_poll_needs_at_least_two_options(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Opinion Polls', 'opinion-polls');
        $this->entitlement($candidate, $tool, 'opinion-polls');

        $this->actingAs($user)
            ->post(route('aspirant.tools.polls.store'), [
                'question' => 'Only one choice?',
                'options' => 'Yes',
            ])
            ->assertSessionHasErrors('options');

        $this->assertDatabaseCount('aspirant_polls', 0);
    }

    public function test_a_call_script_is_saved_and_the_entitlement_is_consumed(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Call Center', 'call-center');
        $this->entitlement($candidate, $tool, 'call-center');

        $this->actingAs($user)
            ->post(route('aspirant.tools.call-center.script'), [
                'script' => 'Good evening, I am calling on behalf of your aspirant to hear your priorities.',
                'callback_priority' => 'supporters',
            ])
            ->assertRedirect(route('aspirant.tools.show', 'call-center'));

        $this->assertDatabaseHas('candidate_call_scripts', [
            'candidate_id' => $candidate->id,
            'callback_priority' => 'supporters',
            'scope_type' => 'county',
            'scope_value' => 'Nairobi',
        ]);

        $this->assertSame(4, CandidateCampaignToolEntitlement::firstOrFail()->remaining_allowance);
    }

    public function test_a_call_log_is_recorded_for_a_scoped_voter_only(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Call Center', 'call-center');
        $this->entitlement($candidate, $tool, 'call-center', 10);
        $voter = $this->voter();
        $outsider = $this->voter(['county' => 'Mombasa']);

        $this->actingAs($user)
            ->postJson(route('aspirant.tools.call-center.calls'), [
                'voter_user_id' => $voter->id,
                'outcome' => 'supporter',
                'notes' => 'Will volunteer next week.',
            ])
            ->assertCreated()
            ->assertJsonPath('log.voter', $voter->name);

        $this->assertDatabaseHas('candidate_call_logs', [
            'candidate_id' => $candidate->id,
            'voter_user_id' => $voter->id,
            'outcome' => 'supporter',
        ]);

        $this->actingAs($user)
            ->postJson(route('aspirant.tools.call-center.calls'), [
                'voter_user_id' => $outsider->id,
                'outcome' => 'reached',
            ])
            ->assertNotFound();
    }

    public function test_the_scoped_call_list_page_renders(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Call Center', 'call-center');
        $this->entitlement($candidate, $tool, 'call-center');
        $this->voter(['name' => 'Scoped Call Voter']);

        $this->actingAs($user)
            ->get(route('aspirant.tools.show', ['key' => 'call-center', 'call_list' => 1]))
            ->assertOk()
            ->assertSee('Scoped Call Voter');
    }

    public function test_a_campaign_website_request_is_submitted(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Campaign Website', 'campaign-website');
        $this->entitlement($candidate, $tool, 'campaign-website');

        $this->actingAs($user)
            ->post(route('aspirant.tools.campaign-website.request'), [
                'candidate_name' => 'Aspirant Owner',
                'website_type' => 'standard',
                'email' => 'owner@example.com',
            ])
            ->assertRedirect(route('aspirant.tools.show', 'campaign-website'));

        $this->assertDatabaseHas('campaign_website_requests', [
            'candidate_id' => $candidate->id,
            'user_id' => $user->id,
            'website_type' => 'standard',
            'status' => 'new',
        ]);
    }

    public function test_the_website_samples_page_only_lists_published_samples(): void
    {
        CampaignWebsiteSample::create(['title' => 'Published Sample Site', 'status' => 'published', 'sort_order' => 1]);
        CampaignWebsiteSample::create(['title' => 'Hidden Sample Site', 'status' => 'draft', 'sort_order' => 2]);

        $this->actingAs($this->aspirantUser())
            ->get(route('aspirant.campaign-website.samples'))
            ->assertOk()
            ->assertSee('Published Sample Site')
            ->assertDontSee('Hidden Sample Site');
    }

    public function test_a_support_contact_needs_an_email_or_phone(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Support Groups', 'support-groups');
        $this->entitlement($candidate, $tool, 'support-groups');
        $groupType = $this->groupType();

        $this->actingAs($user)
            ->post(route('aspirant.tools.support-groups.contacts.store'), [
                'support_group_type_id' => $groupType->id,
                'name' => 'Contact Without Details',
            ])
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('candidate_support_contacts', 0);
    }

    public function test_support_contacts_are_created_updated_and_deleted_through_the_service(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $tool = $this->publishedTool('Support Groups', 'support-groups');
        $this->entitlement($candidate, $tool, 'support-groups', 10);
        $groupType = $this->groupType();

        $this->actingAs($user)
            ->post(route('aspirant.tools.support-groups.contacts.store'), [
                'support_group_type_id' => $groupType->id,
                'name' => 'Mary Wambui',
                'phone' => '0722000111',
            ])
            ->assertRedirect(route('aspirant.tools.show', 'support-groups'));

        $contact = CandidateSupportContact::query()->firstOrFail();

        $this->assertSame($candidate->id, $contact->candidate_id);
        $this->assertSame($user->id, $contact->created_by);

        $this->actingAs($user)
            ->patch(route('aspirant.tools.support-groups.contacts.update', $contact), [
                'support_group_type_id' => $groupType->id,
                'name' => 'Mary W. Kariuki',
                'email' => 'mary@example.com',
            ])
            ->assertRedirect(route('aspirant.tools.show', 'support-groups'));

        $this->assertDatabaseHas('candidate_support_contacts', [
            'id' => $contact->id,
            'name' => 'Mary W. Kariuki',
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete(route('aspirant.tools.support-groups.contacts.destroy', $contact))
            ->assertRedirect(route('aspirant.tools.show', 'support-groups'));

        $this->assertDatabaseCount('candidate_support_contacts', 0);
    }

    public function test_a_candidate_cannot_touch_another_candidates_support_contact(): void
    {
        $owner = $this->aspirantUser();
        $ownerCandidate = $this->candidateFor($owner);
        $this->publishedTool('Support Groups', 'support-groups');
        $groupType = $this->groupType();
        $contact = CandidateSupportContact::create([
            'candidate_id' => $ownerCandidate->id,
            'support_group_type_id' => $groupType->id,
            'name' => 'Owner Contact',
            'phone' => '0733000444',
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
        ]);

        $intruder = $this->aspirantUser();
        $this->candidateFor($intruder);

        $this->actingAs($intruder)
            ->delete(route('aspirant.tools.support-groups.contacts.destroy', $contact))
            ->assertForbidden();

        $this->assertDatabaseHas('candidate_support_contacts', ['id' => $contact->id]);
    }

    public function test_legacy_xls_support_contact_imports_are_rejected(): void
    {
        $user = $this->aspirantUser();
        $this->candidateFor($user);

        $this->actingAs($user)
            ->post(route('aspirant.tools.support-groups.contacts.import'), [
                'contacts_file' => UploadedFile::fake()->create('contacts.xls', 10, 'application/vnd.ms-excel'),
            ])
            ->assertSessionHasErrors('contacts_file');
    }

    public function test_bulk_sms_reserves_tokens_and_queues_the_message(): void
    {
        Queue::fake();

        $user = $this->aspirantUser();
        $candidate = $this->candidateFor($user);
        $this->publishedTool('Bulk SMS', 'bulk-sms');
        $this->smsSetting($candidate);
        $this->voter();

        // Visiting the tool page creates the wallet and grants the initial tokens.
        app(AspirantTokenService::class)->walletForCandidate($candidate);

        $this->actingAs($user)
            ->post(route('aspirant.tools.bulk-sms.send'), [
                'message' => 'Join us at the rally on Saturday.',
                'recipient_source' => 'registered_voters',
                'privacy_acknowledged' => '1',
            ])
            ->assertRedirect(route('aspirant.tools.show', 'bulk-sms'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('candidate_sms_messages', [
            'candidate_id' => $candidate->id,
            'status' => 'queued',
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

        $this->actingAs($user)
            ->post(route('aspirant.tools.bulk-sms.send'), [
                'message' => 'Join us at the rally on Saturday.',
                'recipient_source' => 'registered_voters',
                'privacy_acknowledged' => '1',
            ])
            ->assertRedirect(route('aspirant.tools.show', 'bulk-sms'))
            ->assertSessionHas('warning', 'No valid phone numbers were found for your scoped voters.');

        $this->assertDatabaseCount('candidate_sms_messages', 0);
        Queue::assertNothingPushed();
    }
}
