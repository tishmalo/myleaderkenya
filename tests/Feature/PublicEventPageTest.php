<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicket;
use App\Services\Api\IpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class PublicEventPageTest extends TestCase
{
    use RefreshDatabase;

    private function event(string $title, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => "All about {$title}.",
            'date' => now()->addDay(),
            'location' => 'Nairobi',
            'price' => 3000,
            'is_active' => true,
            'approval_status' => Event::STATUS_APPROVED,
        ], $overrides));
    }

    private function registration(Event $event, array $overrides = []): EventRegistration
    {
        return EventRegistration::create(array_merge([
            'event_id' => $event->id,
            'name' => 'Jane Wanjiku',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
            'user_type' => 'Voter',
            'position' => 'Other',
            'amount' => 3000,
            'quantity' => 2,
            'attendee_names' => ['Peter Otieno'],
            'payment_status' => 'pending',
            'checkout_reference' => 'EVT-TEST-1',
        ], $overrides));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Wanjiku',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
            'quantity' => 2,
            'attendee_names' => ['Peter Otieno'],
            'user_type' => 'Voter',
            'position' => 'Other',
        ], $overrides);
    }

    public function test_the_index_lists_upcoming_and_past_published_events(): void
    {
        $this->event('Upcoming Rally');
        $this->event('Past Rally', ['date' => now()->subDays(3)]);
        $this->event('Pending Rally', [
            'date' => now()->addDays(2),
            'approval_status' => Event::STATUS_PENDING,
        ]);

        $this->get(route('events.public'))
            ->assertOk()
            ->assertSee('Upcoming Rally')
            ->assertSee('Past Rally')
            ->assertDontSee('Pending Rally')
            ->assertSee('<title>Kenya 2027 Election Events &amp; Campaign Rallies | My Leader Kenya</title>', false);
    }

    public function test_the_event_page_shows_the_event_with_seo_and_schema(): void
    {
        $event = $this->event('Nairobi Town Hall');

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('Nairobi Town Hall')
            ->assertSee('<title>Nairobi Town Hall | Kenya 2027 Event</title>', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type": "Event"', false)
            ->assertSee('rel="canonical"', false);
    }

    public function test_only_active_approved_events_are_reachable(): void
    {
        $pending = $this->event('Pending Event', ['approval_status' => Event::STATUS_PENDING]);
        $inactive = $this->event('Inactive Event', ['is_active' => false]);

        $this->get(route('events.show', $pending->slug))->assertNotFound();
        $this->get(route('events.show', $inactive->slug))->assertNotFound();
    }

    public function test_a_ticket_is_viewable_by_its_code(): void
    {
        $registration = $this->registration($this->event('Ticketed Event'));

        $ticket = EventTicket::create([
            'event_registration_id' => $registration->id,
            'attendee_name' => 'Jane Wanjiku',
            'code' => 'TKT-ABC12345',
            'status' => 'issued',
        ]);

        $this->get(route('events.ticket', $ticket->code))
            ->assertOk()
            ->assertSee('TKT-ABC12345')
            ->assertSee('Jane Wanjiku');

        $this->get(route('events.ticket', 'TKT-NOSUCHCO'))->assertNotFound();
    }

    public function test_registering_creates_a_pending_registration_and_redirects_to_the_gateway(): void
    {
        $event = $this->event('Checkout Event');

        $this->mock(IpayService::class, function ($mock): void {
            $mock->shouldReceive('eventCheckoutUrl')
                ->once()
                ->andReturn('https://payments.ipayafrica.com/v3/ke?oid=EVT');
        });

        $this->post(route('events.register', $event->slug), $this->payload())
            ->assertRedirect('https://payments.ipayafrica.com/v3/ke?oid=EVT');

        $registration = EventRegistration::query()->firstOrFail();

        $this->assertSame($event->id, $registration->event_id);
        $this->assertSame('jane@example.com', $registration->email);
        $this->assertSame(2, $registration->quantity);
        $this->assertEquals(6000, (float) $registration->amount);
        $this->assertSame('pending', $registration->payment_status);
        $this->assertNotEmpty($registration->checkout_reference);
    }

    public function test_registration_payload_is_validated(): void
    {
        $event = $this->event('Validated Event');

        $this->post(route('events.register', $event->slug), [])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'quantity', 'user_type', 'position']);

        $this->assertDatabaseCount('event_registrations', 0);
    }

    public function test_registering_for_an_unpublished_event_returns_not_found(): void
    {
        $event = $this->event('Hidden Event', ['approval_status' => Event::STATUS_PENDING]);

        $this->post(route('events.register', $event->slug), $this->payload())
            ->assertNotFound();
    }

    public function test_a_callback_without_a_reference_is_rejected(): void
    {
        $this->mock(IpayService::class, function ($mock): void {
            $mock->shouldReceive('callbackReference')->once()->andReturn(null);
        });

        $this->get(route('events.payment.callback'))
            ->assertRedirect(route('events.public'))
            ->assertSessionHas('warning', 'Callback failed: missing transaction reference.');
    }

    public function test_a_callback_for_an_unknown_reference_is_reported(): void
    {
        $this->mock(IpayService::class, function ($mock): void {
            $mock->shouldReceive('callbackReference')->andReturn('EVT-UNKNOWN');
        });

        $this->get(route('events.payment.callback', ['id' => 'EVT-UNKNOWN']))
            ->assertRedirect(route('events.public'))
            ->assertSessionHas('warning', 'Could not find registration for reference: EVT-UNKNOWN');
    }

    public function test_a_successful_callback_confirms_payment_and_issues_tickets(): void
    {
        Mail::fake();

        $event = $this->event('Paid Event');
        $registration = $this->registration($event);

        $this->mock(IpayService::class, function ($mock): void {
            $mock->shouldReceive('callbackReference')->andReturn('EVT-TEST-1');
            $mock->shouldReceive('verifyTransaction')->with('EVT-TEST-1')->andReturn([
                'status' => 'success',
                'raw' => ['status' => 1],
                'transaction_code' => 'TX-900',
                'amount' => 6000,
            ]);
        });

        $this->get(route('events.payment.callback', ['id' => 'EVT-TEST-1']))
            ->assertRedirect(route('events.show', $event->slug))
            ->assertSessionHas('success');

        $registration->refresh();

        $this->assertSame('success', $registration->payment_status);
        $this->assertSame('TX-900', $registration->payment_reference);
        $this->assertSame(2, $registration->tickets()->count());
    }

    public function test_a_pending_verification_leaves_the_registration_pending(): void
    {
        $event = $this->event('Pending Payment Event');
        $this->registration($event);

        $this->mock(IpayService::class, function ($mock): void {
            $mock->shouldReceive('callbackReference')->andReturn('EVT-TEST-1');
            $mock->shouldReceive('verifyTransaction')->andReturn([
                'status' => 'pending',
                'raw' => [],
                'transaction_code' => null,
                'amount' => null,
            ]);
        });

        $this->get(route('events.payment.callback', ['id' => 'EVT-TEST-1']))
            ->assertRedirect(route('events.show', $event->slug))
            ->assertSessionHas('warning', 'Your payment is still pending. We will confirm it shortly.');

        $this->assertSame('pending', EventRegistration::query()->firstOrFail()->payment_status);
    }

    public function test_an_unreachable_gateway_marks_the_registration_failed(): void
    {
        $event = $this->event('Broken Gateway Event');
        $this->registration($event);

        $this->mock(IpayService::class, function ($mock): void {
            $mock->shouldReceive('callbackReference')->andReturn('EVT-TEST-1');
            $mock->shouldReceive('verifyTransaction')->andThrow(new RuntimeException('Gateway timeout'));
        });

        $this->get(route('events.payment.callback', ['id' => 'EVT-TEST-1']))
            ->assertRedirect(route('events.show', $event->slug))
            ->assertSessionHas('warning', 'Payment transaction failed or was cancelled. Please try again.');

        $this->assertSame('failed', EventRegistration::query()->firstOrFail()->payment_status);
    }
}
