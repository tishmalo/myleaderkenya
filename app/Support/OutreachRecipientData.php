<?php

namespace App\Support;

/**
 * One resolved recipient of an outreach campaign: who they are, where to reach
 * them, and the values their {placeholders} take.
 */
class OutreachRecipientData
{
    /**
     * @param  array<string, string>  $variables
     */
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly array $variables = [],
        public readonly ?string $morphType = null,
        public readonly ?int $morphId = null,
    ) {}

    /**
     * A stable key so the same person is not messaged twice when an audience
     * unions several sources.
     */
    public function key(): string
    {
        $email = strtolower(trim((string) $this->email));
        $phone = preg_replace('/\D+/', '', (string) $this->phone) ?? '';

        if ($email !== '') {
            return 'email:'.$email;
        }

        if ($phone !== '') {
            return 'phone:'.$phone;
        }

        return 'morph:'.($this->morphType ?? 'anon').':'.($this->morphId ?? spl_object_id($this));
    }

    public function hasChannel(string $channel): bool
    {
        return $channel === 'email'
            ? filled($this->email)
            : filled($this->phone);
    }
}
