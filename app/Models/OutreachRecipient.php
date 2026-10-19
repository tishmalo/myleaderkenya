<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OutreachRecipient extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'campaign_id',
        'recipient_type',
        'recipient_id',
        'name',
        'email',
        'phone',
        'status',
        'error',
        'rendered_subject',
        'rendered_body',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            // Recipient addresses are personal data, the same as users.email and
            // users.phone, so they are encrypted at rest here too.
            'email' => 'encrypted',
            'phone' => 'encrypted',
            'sent_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(OutreachCampaign::class, 'campaign_id');
    }

    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }
}
