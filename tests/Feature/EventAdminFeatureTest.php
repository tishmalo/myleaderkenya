<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventAdminFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Town Hall Forum',
            'description' => 'A public town hall forum.',
            'date' => now()->addDay()->format('Y-m-d\TH:i'),
            'location' => 'Nairobi',
            'price' => '3000',
            'promo_video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_active' => '1',
        ], $overrides);
    }

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'title' => 'Existing Event',
            'slug' => 'existing-event',
            'description' => 'Existing event description.',
            'date' => now()->addDays(2),
            'location' => 'Nakuru',
            'price' => 5000,
        ], $overrides));
    }

    public function test_admin_can_view_events_index(): void
    {
        $event = $this->createEvent();

        $this->actingAs($this->admin())
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee($event->title);
    }

    public function test_admin_can_create_event_with_poster_and_youtube_link(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())
            ->post(route('events.store'), $this->validPayload([
                'poster' => UploadedFile::fake()->image('poster.jpg'),
            ]));

        $response->assertRedirect(route('events.index'));

        $event = Event::where('title', 'Town Hall Forum')->first();

        $this->assertNotNull($event);
        $this->assertSame('town-hall-forum', $event->slug);
        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $event->promo_video);
        $this->assertNotNull($event->poster);
        Storage::disk('public')->assertExists($event->poster);
    }

    public function test_create_event_rejects_non_youtube_promo_video(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('events.store'), $this->validPayload([
                'promo_video' => 'https://vimeo.com/123456789',
            ]));

        $response->assertSessionHasErrors('promo_video');
    }

    public function test_admin_can_update_event(): void
    {
        $event = $this->createEvent();

        $response = $this->actingAs($this->admin())
            ->put(route('events.update', $event), $this->validPayload(['title' => 'Updated Forum']));

        $response->assertRedirect(route('events.index'));
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Forum',
            'slug' => 'updated-forum',
        ]);
    }

    public function test_admin_can_delete_event(): void
    {
        $event = $this->createEvent();

        $response = $this->actingAs($this->admin())
            ->deleteJson(route('events.destroy', $event));

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_admin_can_view_event_registrations(): void
    {
        $event = $this->createEvent();

        EventRegistration::create([
            'event_id' => $event->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
            'user_type' => 'Voter',
            'position' => 'MPs',
            'amount' => 5000,
            'payment_status' => 'pending',
            'checkout_reference' => 'EVT-REF-123',
        ]);

        $this->actingAs($this->admin())
            ->get(route('events.registrations', $event))
            ->assertOk()
            ->assertSee('Jane Doe');
    }
}
