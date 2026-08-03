<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateSmsBalanceRequest extends Model implements AuditableContract
{
    use AuditsChanges;
    public const STATUSES = ['new', 'in_review', 'followed_up', 'closed'];

    protected $fillable = [
        'candidate_id',
        'user_id',
        'provider',
        'requested_amount',
        'message',
        'provider_balance_snapshot',
        'status',
        'admin_notes',
        'followed_up_at',
    ];

    protected $casts = [
        'provider_balance_snapshot' => 'array',
        'followed_up_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
