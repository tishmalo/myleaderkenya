<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateSmsMessage extends Model
{
    protected $fillable = [
        'candidate_id',
        'user_id',
        'message',
        'scope_type',
        'scope_column',
        'scope_value',
        'recipient_count',
        'status',
        'token_transaction_id',
        'sms_character_count',
        'sms_encoding',
        'sms_segment_count',
        'sms_unit_count',
        'token_cost',
        'provider_response',
        'sent_at',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'sent_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tokenTransaction(): BelongsTo
    {
        return $this->belongsTo(CandidateTokenTransaction::class, 'token_transaction_id');
    }
}


