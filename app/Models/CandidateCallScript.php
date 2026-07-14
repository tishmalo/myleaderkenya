<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateCallScript extends Model
{
    protected $fillable = [
        'candidate_id',
        'user_id',
        'script',
        'callback_priority',
        'scope_type',
        'scope_column',
        'scope_value',
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
