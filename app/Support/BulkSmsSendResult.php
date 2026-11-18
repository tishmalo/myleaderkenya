<?php

namespace App\Support;

use App\Models\CandidateSmsMessage;

final readonly class BulkSmsSendResult
{
    /**
     * @param  array<string, mixed>  $quote
     */
    private function __construct(
        public ?string $warning,
        public int $recipientCount = 0,
        public int $tokensReserved = 0,
        public int $smsUnits = 0,
        public int $status = 422,
        public ?CandidateSmsMessage $message = null,
        public array $quote = [],
    ) {}

    public static function warning(string $message, int $status = 422): self
    {
        return new self($message, status: $status);
    }

    /**
     * @param  array<string, mixed>  $quote
     */
    public static function sent(int $recipientCount, int $tokensReserved, int $smsUnits, CandidateSmsMessage $message, array $quote): self
    {
        return new self(
            warning: null,
            recipientCount: $recipientCount,
            tokensReserved: $tokensReserved,
            smsUnits: $smsUnits,
            status: 201,
            message: $message,
            quote: $quote,
        );
    }

    public function failed(): bool
    {
        return $this->warning !== null;
    }
}
