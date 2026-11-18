<?php

namespace App\Support;

final readonly class BulkSmsSendResult
{
    private function __construct(
        public ?string $warning,
        public int $recipientCount = 0,
        public int $tokensReserved = 0,
        public int $smsUnits = 0,
    ) {}

    public static function warning(string $message): self
    {
        return new self($message);
    }

    public static function sent(int $recipientCount, int $tokensReserved, int $smsUnits): self
    {
        return new self(null, $recipientCount, $tokensReserved, $smsUnits);
    }

    public function failed(): bool
    {
        return $this->warning !== null;
    }
}
