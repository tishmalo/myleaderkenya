<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\PublicEventRepositoryInterface;
use App\Models\Event;
use App\Services\Api\IpayService;
use App\Support\EventPaymentOutcome;
use Throwable;

/**
 * The paid side of event registration: turning a submitted form into a gateway
 * redirect, and turning the gateway's callback back into a registration state.
 */
class EventCheckoutService
{
    public function __construct(
        private EventRegistrationService $registrations,
        private PublicEventRepositoryInterface $events,
        private IpayService $ipay,
    ) {}

    /**
     * Register the attendee and return the URL to send them to for payment.
     */
    public function start(Event $event, array $data): string
    {
        $registration = $this->registrations->register($event, $data);

        return $this->ipay->eventCheckoutUrl($registration, [
            'phone' => $data['phone'],
            'email' => $data['email'],
        ]);
    }

    /**
     * Apply a gateway callback to the registration it refers to.
     */
    public function complete(array $callbackData): EventPaymentOutcome
    {
        $reference = $this->ipay->callbackReference($callbackData);

        if (! $reference) {
            return new EventPaymentOutcome(EventPaymentOutcome::MISSING_REFERENCE);
        }

        $registration = $this->events->registrationByCheckoutReference($reference);

        if (! $registration) {
            return new EventPaymentOutcome(EventPaymentOutcome::UNKNOWN_REGISTRATION, null, $reference);
        }

        $registration = $this->registrations->confirmPayment($registration, $this->verify($reference), $callbackData);

        if ($registration->payment_status === EventPaymentOutcome::SUCCESS) {
            $this->registrations->sendTicketEmail($registration);
        }

        return new EventPaymentOutcome($registration->payment_status, $registration, $reference);
    }

    /**
     * Ask the gateway what happened. A gateway that cannot be reached is a
     * failed transaction, not a 500 for the payer.
     *
     * @return array{status: string, raw: mixed, transaction_code: ?string, amount: mixed}
     */
    private function verify(string $reference): array
    {
        try {
            return $this->ipay->verifyTransaction($reference);
        } catch (Throwable $e) {
            return [
                'status' => EventPaymentOutcome::FAILED,
                'raw' => ['error' => $e->getMessage()],
                'transaction_code' => null,
                'amount' => null,
            ];
        }
    }
}
