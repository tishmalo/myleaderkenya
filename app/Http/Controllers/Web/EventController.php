<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\Api\IpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Throwable;

class EventController extends Controller
{
    public function __construct(
        private IpayService $ipayService
    ) {}

    public function index()
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

    public function show($slug)
    {
        $event = Event::active()->where('slug', $slug)->firstOrFail();
        return view('events.show', compact('event'));
    }

    public function register(Request $request, $slug)
    {
        $event = Event::active()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'user_type' => 'required|string|in:Aspirant,Campaign Manager,Voter,Party Representative,Trainers,Ambassador',
            'position' => 'required|string|in:President,Governor,Senator,Women Rep,MPs,MCAs,Other',
        ]);

        $checkoutReference = 'EVT' . now()->format('YmdHis') . Str::upper(Str::random(6));

        try {
            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'user_type' => $validated['user_type'],
                'position' => $validated['position'],
                'amount' => $event->price,
                'payment_status' => 'pending',
                'checkout_reference' => $checkoutReference,
            ]);

            $checkoutUrl = $this->ipayService->eventCheckoutUrl($registration, [
                'phone' => $validated['phone'],
                'email' => $validated['email']
            ]);

            return redirect()->away($checkoutUrl);

        } catch (Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('warning', 'Could not initiate payment: ' . $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        $reference = $this->ipayService->callbackReference($request->query());

        if (!$reference) {
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
                'amount' => null
            ];
        }

        $registration = EventRegistration::where('checkout_reference', $reference)->first();

        if (!$registration) {
            return redirect()->route('events.public')
                ->with('warning', 'Could not find registration for reference: ' . $reference);
        }

        $event = $registration->event;

        DB::transaction(function () use ($registration, $verification, $request) {
            $gatewayUpdate = [
                'payment_reference' => $verification['transaction_code'] ?? $registration->payment_reference,
                'gateway_response' => [
                    'callback' => $request->query(),
                    'verification' => $verification['raw'] ?? null,
                ]
            ];

            if ($verification['status'] === 'success') {
                $registration->update(array_merge($gatewayUpdate, [
                    'payment_status' => 'success'
                ]));
            } else {
                $status = $verification['status'] === 'pending' ? 'pending' : 'failed';
                $registration->update(array_merge($gatewayUpdate, [
                    'payment_status' => $status
                ]));
            }
        });

        if ($registration->fresh()->payment_status === 'success') {
            return redirect()->route('events.show', $event->slug)
                ->with('success', 'Thank you! Your payment of KES ' . number_format($registration->amount) . ' is confirmed, and your registration for "' . $event->title . '" is successful.');
        }

        if ($registration->fresh()->payment_status === 'pending') {
            return redirect()->route('events.show', $event->slug)
                ->with('warning', 'Your payment is still pending. We will confirm it shortly.');
        }

        return redirect()->route('events.show', $event->slug)
            ->with('warning', 'Payment transaction failed or was cancelled. Please try again.');
    }
}
