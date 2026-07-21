<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CandidateTokenTransaction extends Model
{
    protected $fillable = [
        'candidate_id',
        'candidate_token_wallet_id',
        'user_id',
        'candidate_token_purchase_id',
        'tokenable_type',
        'tokenable_id',
        'type',
        'status',
        'action_key',
        'action_label',
        'calculation_type',
        'quantity',
        'unit_tokens',
        'amount',
        'balance_before',
        'balance_after',
        'metadata',
        'finalized_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'finalized_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(CandidateTokenWallet::class, 'candidate_token_wallet_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(CandidateTokenPurchase::class, 'candidate_token_purchase_id');
    }

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }
}
