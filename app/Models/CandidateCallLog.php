<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateCallLog extends Model
{
    protected $fillable = [
        'candidate_id',
        'user_id',
        'voter_user_id',
        'voter_name',
        'voter_phone',
        'outcome',
        'notes',
        'callback_at',
        'scope_type',
        'scope_column',
        'scope_value',
        'called_at',
    ];

    protected $casts = [
        'callback_at' => 'datetime',
        'called_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voter_user_id');
    }
}
