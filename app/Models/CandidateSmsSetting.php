<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateSmsSetting extends Model
{
    protected $fillable = [
        'candidate_id',
        'enabled',
        'provider',
        'base_url',
        'api_key',
        'sender_name',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'base_url' => 'encrypted',
        'api_key' => 'encrypted',
        'sender_name' => 'encrypted',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function isReady(): bool
    {
        return $this->enabled
            && $this->provider === 'infobip'
            && filled($this->base_url)
            && filled($this->api_key)
            && filled($this->sender_name);
    }
}
