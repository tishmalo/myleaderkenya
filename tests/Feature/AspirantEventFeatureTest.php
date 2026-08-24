<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AspirantEventFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function aspirant(): User
    {
        return User::factory()->create([
            'is_aspirant' => true,
            'gender' => 'male',
            'year_of_birth' => 1995,
            'county' => 'Nairobi',
            'constituency' => 'Westlands',
            'ward' => 'Kitisuru',
            'country_of_residence' => 'Kenya',
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Youth Town Hall',
            'description' => 'A community forum for young voters.',
            'date' => now()->addWeek()->format('Y-m-d\TH:i'),
            'location' => 'Nairobi',
        ], $overrides);
    }

    public function test_aspirant_can_submit_event_and_it_stays_pending(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->aspirant())
            ->post(route('aspirant.events.store'), $this->validPayload([
                'poster' => UploadedFile::fake()->image('poster.jpg'),
            ]));

        $response->assertRedirect(route('aspirant.events.index'));

        $event = Event::where('title', 'Youth Town Hall')->first();

        $this->assertNotNull($event);
        $this->assertSame(Event::STATUS_PENDING, $event->approval_status);
        $this->assertTrue((bool) $event->is_active);
        $this->assertNotNull($event->poster);
        Storage::disk('public')->assertExists($event->poster);

        $this->assertDatabaseMissing('events', [
            'title' => 'Youth Town Hall',
            'reviewed_by' => null,
            'approval_status' => Event::STATUS_APPROVED,
        ]);
    }

    public function test_pending_event_is_hidden_from_public_events_page(): void
    {
        Event::create([
            'title' => 'Hidden Pending Forum',
            'slug' => 'hidden-pending-forum',
            'description' => 'Not yet approved.',
            'date' => now()->addDays(3),
            'location' => 'Kisumu',
            'price' => 1000,
            'approval_status' => Event::STATUS_PENDING,
        ]);

        $this->get(route('events.public'))
            ->assertOk()
            ->assertDontSee('Hidden Pending Forum');

        $this->get(route('events.show', 'hidden-pending-forum'))
            ->assertNotFound();
    }

    public function test_approved_event_is_visible_on_public_events_page(): void
    {
        Event::create([
            'title' => 'Visible Approved Forum',
            'slug' => 'visible-approved-forum',
            'description' => 'Approved and public.',
            'date' => now()->addDays(3),
            'location' => 'Mombasa',
            'price' => 1000,
            'approval_status' => Event::STATUS_APPROVED,
        ]);

        $this->get(route('events.public'))
            ->assertOk()
            ->assertSee('Visible Approved Forum');
    }

    public function test_admin_can_approve_a_submitted_event(): void
    {
        $admin = $this->admin();

        $event = Event::create([
            'title' => 'Submitted Forum',
            'slug' => 'submitted-forum',
            'description' => 'Awaiting review.',
            'date' => now()->addDays(3),
            'location' => 'Eldoret',
            'price' => 3000,
            'approval_status' => Event::STATUS_PENDING,
            'created_by' => $this->aspirant()->id,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('events.index'))
            ->patch(route('events.approval', $event), ['status' => 'approved']);

        $response->assertRedirect(route('events.index'));

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'approval_status' => Event::STATUS_APPROVED,
            'reviewed_by' => $admin->id,
        ]);

        $this->assertNotNull($event->fresh()->reviewed_at);
    }

    public function test_admin_can_reject_a_submitted_event(): void
    {
        $admin = $this->admin();

        $event = Event::create([
            'title' => 'Spammy Forum',
            'slug' => 'spammy-forum',
            'description' => 'Awaiting review.',
            'date' => now()->addDays(3),
            'location' => 'Online',
            'price' => 3000,
            'approval_status' => Event::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('events.index'))
            ->patch(route('events.approval', $event), ['status' => 'rejected']);

        $response->assertRedirect(route('events.index'));

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'approval_status' => Event::STATUS_REJECTED,
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_admin_approval_rejects_invalid_status(): void
    {
        $event = Event::create([
            'title' => 'Some Forum',
            'slug' => 'some-forum',
            'description' => 'Awaiting review.',
            'date' => now()->addDays(3),
            'location' => 'Nakuru',
            'price' => 3000,
            'approval_status' => Event::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin())
            ->patch(route('events.approval', $event), ['status' => 'maybe'])
            ->assertSessionHasErrors('status');
    }

    public function test_submission_rejects_past_dates(): void
    {
        $response = $this->actingAs($this->aspirant())
            ->post(route('aspirant.events.store'), $this->validPayload([
                'date' => now()->subDay()->format('Y-m-d\TH:i'),
            ]));

        $response->assertSessionHasErrors('date');
    }

    public function test_guest_cannot_access_aspirant_event_pages(): void
    {
        $this->get(route('aspirant.events.create'))->assertRedirect(route('login'));
        $this->post(route('aspirant.events.store'), $this->validPayload())->assertRedirect(route('login'));
    }
}
