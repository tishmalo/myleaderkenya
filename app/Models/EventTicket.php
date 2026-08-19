<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventTicket extends Model
{
    protected $fillable = [
        'event_registration_id',
        'attendee_name',
        'code',
        'status',
        'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id');
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    public function verificationUrl(): string
    {
        return route('events.ticket', $this->code);
    }

    public function qrCodeDataUri(): string
    {
        $svg = QrCode::format('svg')->size(220)->margin(1)->generate($this->verificationUrl());

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
