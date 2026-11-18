<?php

namespace App\Support;

use App\Models\EventRegistration;

/**
 * The result of a payment gateway callback: how far the callback got, and the
 * registration it resolved to when there was one to update.
 */
final class EventPaymentOutcome
{
    public const MISSING_REFERENCE = 'missing_reference';

    public const UNKNOWN_REGISTRATION = 'unknown_registration';

    public const SUCCESS = 'success';

    public const PENDING = 'pending';

    public const FAILED = 'failed';

    /**
     * @param  string  $status  One of this class's status constants; after a
     *                          resolved registration it mirrors its payment_status.
     */
    public function __construct(
        public readonly string $status,
        public readonly ?EventRegistration $registration = null,
        public readonly ?string $reference = null,
    ) {}

    public function eventSlug(): ?string
    {
        return $this->registration?->event?->slug;
    }
}
