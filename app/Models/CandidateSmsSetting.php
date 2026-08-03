<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateSmsSetting extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = [
        'candidate_id',
        'enabled',
        'provider',
        'base_url',
        'username',
        'password',
        'sender_name',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'base_url' => 'encrypted',
        'username' => 'encrypted',
        'password' => 'encrypted',
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
            && filled($this->username)
            && filled($this->password)
            && filled($this->sender_name);
    }
}
