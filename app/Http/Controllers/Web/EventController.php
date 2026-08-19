<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicket;
use App\Services\Api\IpayService;
use App\Services\Web\EventRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class EventController extends Controller
{
    public function __construct(
        private IpayService $ipayService,
        private EventRegistrationService $registrationService
    ) {}

    public function index(): View
    {
        $upcomingEvents = Event::active()
            ->where('date', '>=', now())
            ->orderBy('date', 'asc')
            ->get();

        $pastEvents = Event::active()
            ->where('date', '<', now())
            ->orderBy('date', 'desc')
            ->take(6)
            ->get();

        return view('events.index', compact('upcomingEvents', 'pastEvents'));
    }

    public function show(string $slug): View
    {
        $event = Event::active()->where('slug', $slug)->firstOrFail();

        return view('events.show', compact('event'));
    }

    public function ticket(string $code): View
    {
        $ticket = EventTicket::with('registration.event')
            ->where('code', $code)
            ->firstOrFail();

        return view('events.ticket', compact('ticket'));
    }

    public function register(Request $request, string $slug): RedirectResponse
    {
        $event = Event::active()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'quantity' => 'required|integer|min:1|max:10',
            'attendee_names' => 'nullable|array|max:9',
            'attendee_names.*' => 'nullable|string|max:255',
            'user_type' => 'required|string|in:Aspirant,Campaign Manager,Voter,Party Representative,Trainers,Ambassador',
            'position' => 'required|string|in:President,Governor,Senator,Women Rep,MPs,MCAs,Other',
        ]);

        try {
            $registration = $this->registrationService->register($event, $validated);

            $checkoutUrl = $this->ipayService->eventCheckoutUrl($registration, [
                'phone' => $validated['phone'],
                'email' => $validated['email'],
            ]);

            return redirect()->away($checkoutUrl);
        } catch (Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('warning', 'Could not initiate payment: ' . $e->getMessage());
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        $reference = $this->ipayService->callbackReference($request->query());

        if (! $reference) {
            return redirect()->route('events.public')
                ->with('warning', 'Callback failed: missing transaction reference.');
        }

        try {
            $verification = $this->ipayService->verifyTransaction($reference);
        } catch (Throwable $e) {
            $verification = [
                'status' => 'failed',
                'raw' => ['error' => $e->getMessage()],
                'transaction_code' => null,
                'amount' => null,
            ];
        }

        $registration = EventRegistration::where('checkout_reference', $reference)->first();

        if (! $registration) {
            return redirect()->route('events.public')
                ->with('warning', 'Could not find registration for reference: ' . $reference);
        }

        $event = $registration->event;

        $registration = $this->registrationService->confirmPayment($registration, $verification, $request->query());

        if ($registration->payment_status === 'success') {
            $this->registrationService->sendTicketEmail($registration);

            return redirect()->route('events.show', $event->slug)
                ->with('success', 'Thank you! Your payment of KES ' . number_format($registration->amount) . ' is confirmed. Your ticket(s) have been sent to ' . $registration->email . '.');
        }

        if ($registration->payment_status === 'pending') {
            return redirect()->route('events.show', $event->slug)
                ->with('warning', 'Your payment is still pending. We will confirm it shortly.');
        }

        return redirect()->route('events.show', $event->slug)
            ->with('warning', 'Payment transaction failed or was cancelled. Please try again.');
    }
}
