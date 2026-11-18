<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\EventRegistrationRequest;
use App\Services\Web\EventCheckoutService;
use App\Services\Web\PublicEventService;
use App\Support\EventPaymentOutcome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class EventController extends Controller
{
    public function __construct(
        private PublicEventService $events,
        private EventCheckoutService $checkout,
    ) {}

    public function index(): View
    {
        return view('events.index', $this->events->indexData());
    }

    public function show(string $slug): View
    {
        return view('events.show', $this->events->showData($slug));
    }

    public function ticket(string $code): View
    {
        return view('events.ticket', ['ticket' => $this->events->ticket($code)]);
    }

    public function register(EventRegistrationRequest $request, string $slug): RedirectResponse
    {
        $event = $this->events->publishedEvent($slug);

        try {
            return redirect()->away($this->checkout->start($event, $request->validated()));
        } catch (Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('warning', 'Could not initiate payment: '.$e->getMessage());
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        $outcome = $this->checkout->complete($request->query());

        return match ($outcome->status) {
            EventPaymentOutcome::MISSING_REFERENCE => redirect()->route('events.public')
                ->with('warning', 'Callback failed: missing transaction reference.'),
            EventPaymentOutcome::UNKNOWN_REGISTRATION => redirect()->route('events.public')
                ->with('warning', 'Could not find registration for reference: '.$outcome->reference),
            EventPaymentOutcome::SUCCESS => redirect()->route('events.show', $outcome->eventSlug())
                ->with('success', 'Thank you! Your payment of KES '.number_format((float) $outcome->registration->amount).' is confirmed. Your ticket(s) have been sent to '.$outcome->registration->email.'.'),
            EventPaymentOutcome::PENDING => redirect()->route('events.show', $outcome->eventSlug())
                ->with('warning', 'Your payment is still pending. We will confirm it shortly.'),
            default => redirect()->route('events.show', $outcome->eventSlug())
                ->with('warning', 'Payment transaction failed or was cancelled. Please try again.'),
        };
    }
}
