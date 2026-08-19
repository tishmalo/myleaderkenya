<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'email',
        'phone',
        'user_type',
        'position',
        'amount',
        'quantity',
        'attendee_names',
        'payment_status',
        'checkout_reference',
        'payment_reference',
        'gateway_response',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'attendee_names' => 'array',
        'amount' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(EventTicket::class);
    }
}
