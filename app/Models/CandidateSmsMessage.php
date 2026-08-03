<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateSmsMessage extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = [
        'candidate_id',
        'user_id',
        'message',
        'recipient_source',
        'support_group_type_id',
        'privacy_acknowledged_at',
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
        'privacy_acknowledged_at' => 'datetime',
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
