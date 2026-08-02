<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CandidateTokenWallet extends Model
{
    protected $fillable = ['candidate_id', 'balance', 'initial_granted_at'];

    protected $casts = [
        'initial_granted_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CandidateTokenTransaction::class);
    }
}
