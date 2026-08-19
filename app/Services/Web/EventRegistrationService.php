<?php

namespace App\Services\Web;

use App\Mail\EventTicketMail;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicket;
use App\Services\Admin\SettingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EventRegistrationService
{
    public function __construct(private SettingService $settingService) {}

    public function register(Event $event, array $data): EventRegistration
    {
        $quantity = max(1, min(10, (int) ($data['quantity'] ?? 1)));

        return EventRegistration::create([
            'event_id' => $event->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'user_type' => $data['user_type'],
            'position' => $data['position'],
            'amount' => $event->price * $quantity,
            'quantity' => $quantity,
            'attendee_names' => $this->normalizeAttendeeNames($data['attendee_names'] ?? []),
            'payment_status' => 'pending',
            'checkout_reference' => 'EVT' . now()->format('YmdHis') . Str::upper(Str::random(6)),
        ]);
    }

    public function confirmPayment(EventRegistration $registration, array $verification, array $callbackData): EventRegistration
    {
        DB::transaction(function () use ($registration, $verification, $callbackData): void {
            $gatewayUpdate = [
                'payment_reference' => $verification['transaction_code'] ?? $registration->payment_reference,
                'gateway_response' => [
                    'callback' => $callbackData,
                    'verification' => $verification['raw'] ?? null,
                ],
            ];

            $status = match ($verification['status']) {
                'success' => 'success',
                'pending' => 'pending',
                default => 'failed',
            };

            $registration->update(array_merge($gatewayUpdate, ['payment_status' => $status]));

            if ($status === 'success') {
                $this->issueTickets($registration);
            }
        });

        return $registration->fresh();
    }

    public function issueTickets(EventRegistration $registration): void
    {
        if ($registration->tickets()->exists()) {
            return;
        }

        foreach ($this->ticketNames($registration) as $name) {
            EventTicket::create([
                'event_registration_id' => $registration->id,
                'attendee_name' => $name,
                'code' => $this->uniqueCode(),
                'status' => 'issued',
            ]);
        }
    }

    public function ensureTickets(EventRegistration $registration): void
    {
        if ($registration->payment_status === 'success') {
            $this->issueTickets($registration);
        }
    }

    public function sendTicketEmail(EventRegistration $registration): void
    {
        $template = $this->settingService->notificationTemplate('event-ticket');

        if (! $template) {
            return;
        }

        $this->ensureTickets($registration);
        $registration->loadMissing('tickets', 'event');

        Mail::to($registration->email)
            ->send(new EventTicketMail($registration, $template));
    }

    public function toggleCheckIn(EventTicket $ticket): bool
    {
        if ($ticket->checked_in_at) {
            $ticket->update(['checked_in_at' => null, 'status' => 'issued']);

            return false;
        }

        $ticket->update(['checked_in_at' => now(), 'status' => 'checked_in']);

        return true;
    }

    private function ticketNames(EventRegistration $registration): array
    {
        $names = [$registration->name];

        for ($i = 2; $i <= $registration->quantity; $i++) {
            $names[] = $registration->attendee_names[$i - 2] ?? "Guest {$i}";
        }

        return $names;
    }

    private function normalizeAttendeeNames(array $names): array
    {
        return array_values(array_filter(array_map('trim', $names), fn ($name) => $name !== ''));
    }

    private function uniqueCode(): string
    {
        do {
            $code = 'TKT-' . Str::upper(Str::random(8));
        } while (EventTicket::where('code', $code)->exists());

        return $code;
    }
}
