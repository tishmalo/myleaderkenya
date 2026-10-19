<?php

namespace Tests\Feature;

use App\Jobs\SendOutreachCampaign;
use App\Mail\OutreachMail;
use App\Models\Candidate;
use App\Models\CandidateSupportContact;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\OutreachCampaign;
use App\Models\OutreachRecipient;
use App\Models\PoliticalParty;
use App\Models\Position;
use App\Models\Role;
use App\Models\SupportGroupType;
use App\Models\User;
use App\Services\Admin\OutreachAudienceResolver;
use App\Services\Admin\OutreachService;
use App\Support\MessageTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Communications -> Send Emails / Send SMS: audiences, placeholders, delivery
 * and the per-recipient report.
 */
class OutreachMessagingTest extends TestCase
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

    private function position(string $name, int $order): Position
    {
        return Position::firstOrCreate(['name' => $name], ['description' => $name, 'sort_order' => $order]);
    }

    private function aspirant(string $name, string $position, array $attributes = []): Candidate
    {
        return Candidate::create(array_merge([
            'name' => $name,
            'position_id' => $this->position($position, 10)->id,
            'country' => 'Kenya',
            'county' => 'Nairobi',
            'constituency' => 'Dagoretti North',
            'ward' => 'Kileleshwa',
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'phone' => '+254712345678',
            'approval_status' => 'approved',
        ], $attributes));
    }

    public function test_the_two_compose_screens_load_with_audiences_and_placeholders(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get(route('outreach.email'))
            ->assertOk()
            ->assertSee('Send Email')
            ->assertSee('All Aspirants')
            ->assertSee('All Voters')
            ->assertSee('Registered Voters')
            ->assertSee('Non Registered Voters')
            ->assertSee('All Governors')
            ->assertSee('All Senators')
            ->assertSee('All Women Reps')
            ->assertSee('All MPs')
            ->assertSee('All MCAs')
            ->assertSee('All Parties')
            ->assertSee('All Event Attendees')
            ->assertSee('All Admins')
            ->assertSee('All Support Groups')
            ->assertSee('{aspirant_profile_link}', false)
            ->assertSee('{county}', false)
            ->assertSee('{position}', false);

        $this->actingAs($admin)
            ->get(route('outreach.sms'))
            ->assertOk()
            ->assertSee('Send SMS');
    }

    public function test_audiences_resolve_to_the_right_people(): void
    {
        $resolver = app(OutreachAudienceResolver::class);

        $this->aspirant('Sam Governor', 'Governor');
        $this->aspirant('Sally Senator', 'Senator');
        $this->aspirant('Maggie Mca', 'MCA');

        $registered = User::factory()->create(['username' => 'registered-one', 'is_registered' => true, 'email' => 'reg@example.test']);
        $unregistered = User::factory()->create(['username' => 'plain-one', 'is_registered' => false, 'is_voter' => false, 'email' => 'plain@example.test']);

        PoliticalParty::create(['name' => 'ODM', 'abbreviation' => 'ODM', 'content' => 'x', 'status' => 'published', 'email_1' => 'party@example.test', 'phone_1' => '+254700000001']);

        $event = Event::create([
            'title' => 'Town Hall',
            'slug' => 'town-hall',
            'description' => 'x',
            'date' => now()->addWeek(),
            'location' => 'Nairobi',
            'price' => 0,
            'approval_status' => Event::STATUS_APPROVED,
        ]);
        EventRegistration::create([
            'event_id' => $event->id,
            'name' => 'Attendee One',
            'email' => 'attendee@example.test',
            'phone' => '254700000002',
            'user_type' => 'voter',
            'position' => 'Member',
            'amount' => 0,
            'payment_status' => 'paid',
            'checkout_reference' => 'CHK-1',
        ]);

        $group = SupportGroupType::create(['name' => 'Boda Boda', 'slug' => 'boda-boda', 'is_active' => true]);
        CandidateSupportContact::create([
            'candidate_id' => Candidate::first()->id,
            'support_group_type_id' => $group->id,
            'name' => 'Contact One',
            'email' => 'contact@example.test',
            'phone' => '+254700000003',
        ]);

        $this->assertSame(['Sam Governor'], $resolver->resolve('all_governors')->pluck('name')->all());
        $this->assertSame(['Sally Senator'], $resolver->resolve('all_senators')->pluck('name')->all());
        $this->assertSame(['Maggie Mca'], $resolver->resolve('all_mcas')->pluck('name')->all());
        $this->assertTrue($resolver->resolve('registered_voters')->pluck('name')->contains($registered->name));
        $this->assertFalse($resolver->resolve('registered_voters')->pluck('name')->contains($unregistered->name));
        $this->assertTrue($resolver->resolve('non_registered_voters')->pluck('name')->contains($unregistered->name));
        $this->assertSame(['party@example.test'], $resolver->resolve('all_parties')->pluck('email')->all());
        $this->assertSame(['attendee@example.test'], $resolver->resolve('all_event_attendees')->pluck('email')->all());
        $this->assertSame(['contact@example.test'], $resolver->resolve('all_support_groups')->pluck('email')->all());
    }

    public function test_placeholders_render_per_recipient(): void
    {
        $this->aspirant('Jane Wanjiku', 'Governor');

        $rendered = MessageTemplate::render(
            'Hi {name}, your {position} profile for {county} is live: {aspirant_profile_link} ({party})',
            app(OutreachAudienceResolver::class)->resolve('all_governors')->first()->variables
        );

        $this->assertStringContainsString('Hi Jane Wanjiku', $rendered);
        $this->assertStringContainsString('your Governor profile for Nairobi', $rendered);
        $this->assertStringContainsString(route('aspirants.show', Candidate::first()), $rendered);
        $this->assertStringNotContainsString('{', $rendered);
    }

    public function test_an_email_campaign_is_stored_per_recipient_and_delivered(): void
    {
        Mail::fake();
        Bus::fake();

        $admin = $this->superAdmin();
        $this->aspirant('Jane Wanjiku', 'Governor');
        $this->aspirant('Peter Kamau', 'Senator');

        $this->actingAs($admin)
            ->post(route('outreach.store'), [
                'channel' => 'email',
                'audience' => 'all_aspirants',
                'subject' => 'Your profile is live',
                'body' => 'Hi {name}, your {position} profile for {county} is live: {aspirant_profile_link}',
            ])
            ->assertRedirect();

        Bus::assertDispatched(SendOutreachCampaign::class);

        $campaign = OutreachCampaign::firstOrFail();
        $this->assertSame(2, $campaign->total_recipients);
        $this->assertSame(2, $campaign->recipients()->count());

        $jane = $campaign->recipients()->where('name', 'Jane Wanjiku')->firstOrFail();
        $this->assertStringContainsString('Hi Jane Wanjiku, your Governor profile for Nairobi is live', (string) $jane->rendered_body);
        $this->assertSame('Your profile is live', $jane->rendered_subject);
        $this->assertNotSame('jane.wanjiku@example.test', $jane->getRawOriginal('email'), 'Recipient addresses are encrypted at rest.');

        // Delivering records who received it.
        app(OutreachService::class)->send($campaign);

        Mail::assertSent(OutreachMail::class, 2);

        $campaign->refresh();
        $this->assertSame(OutreachCampaign::STATUS_SENT, $campaign->status);
        $this->assertSame(2, $campaign->sent_count);
        $this->assertSame(0, $campaign->failed_count);
        $this->assertSame(2, $campaign->recipients()->where('status', OutreachRecipient::STATUS_SENT)->count());
        $this->assertNotNull($campaign->recipients()->first()->sent_at);
    }

    public function test_the_report_lists_each_person_and_their_status(): void
    {
        Mail::fake();
        $admin = $this->superAdmin();
        $this->aspirant('Jane Wanjiku', 'Governor');

        app(OutreachService::class)->send(
            app(OutreachService::class)->create('email', [
                'audience' => 'all_aspirants',
                'subject' => 'Hello',
                'body' => 'Hi {name}',
            ], $admin)
        );

        $campaign = OutreachCampaign::firstOrFail();

        $this->actingAs($admin)
            ->get(route('outreach.show', $campaign))
            ->assertOk()
            ->assertSee('Email send report')
            ->assertSee('All Aspirants')
            ->assertSee('Jane Wanjiku')
            ->assertSee('jane.wanjiku@example.test')
            ->assertSee('Sent');
    }

    public function test_an_sms_campaign_reports_a_missing_provider_per_recipient(): void
    {
        config(['sms.infobip.base_url' => null, 'sms.infobip.username' => null, 'sms.infobip.password' => null, 'sms.infobip.sender' => null]);

        $admin = $this->superAdmin();
        $this->aspirant('Jane Wanjiku', 'Governor');

        $campaign = app(OutreachService::class)->create('sms', [
            'audience' => 'all_aspirants',
            'body' => 'Hi {name}, your {position} profile is live.',
        ], $admin);

        app(OutreachService::class)->send($campaign);

        $campaign->refresh();
        $this->assertSame(OutreachCampaign::STATUS_FAILED, $campaign->status);
        $this->assertSame(1, $campaign->failed_count);

        $recipient = $campaign->recipients()->firstOrFail();
        $this->assertSame(OutreachRecipient::STATUS_FAILED, $recipient->status);
        $this->assertStringContainsString('not configured', (string) $recipient->error);
    }

    public function test_an_sms_campaign_is_delivered_through_the_platform_account(): void
    {
        config([
            'sms.infobip.base_url' => 'https://sms.test',
            'sms.infobip.username' => 'user',
            'sms.infobip.password' => 'pass',
            'sms.infobip.sender' => 'MLK',
        ]);
        Http::fake(['https://sms.test/*' => Http::response(['messages' => [['status' => 'PENDING']]], 200)]);

        $admin = $this->superAdmin();
        $this->aspirant('Jane Wanjiku', 'Governor');

        $campaign = app(OutreachService::class)->create('sms', [
            'audience' => 'all_aspirants',
            'body' => 'Hi {name}, your {position} profile is live.',
        ], $admin);

        app(OutreachService::class)->send($campaign);

        $campaign->refresh();
        $this->assertSame(OutreachCampaign::STATUS_SENT, $campaign->status);
        $this->assertSame(1, $campaign->sent_count);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://sms.test/restapi/sms/1/text/single'
                && $request['from'] === 'MLK'
                && str_contains((string) $request['text'], 'Hi Jane Wanjiku');
        });
    }

    public function test_an_empty_audience_is_rejected_with_a_clear_message(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->post(route('outreach.store'), [
                'channel' => 'email',
                'audience' => 'all_governors',
                'subject' => 'Nobody',
                'body' => 'Hello',
            ])
            ->assertSessionHasErrors('audience');

        $this->assertDatabaseCount('outreach_campaigns', 0);
    }

    public function test_sending_needs_the_messages_permission(): void
    {
        $clerk = User::factory()->create([
            'name' => 'Clerk Kim',
            'username' => 'clerk-kim',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::ADMIN),
        ]);

        Role::query()->where('name', Role::ADMIN)->firstOrFail()
            ->permissions()->detach(\App\Models\Permission::query()->whereIn('name', ['messages.view', 'messages.create'])->pluck('id')->all());

        $this->actingAs($clerk->fresh())
            ->post(route('outreach.store'), [
                'channel' => 'email',
                'audience' => 'all_aspirants',
                'subject' => 'Hello',
                'body' => 'Hi',
            ])
            ->assertForbidden();
    }
}
