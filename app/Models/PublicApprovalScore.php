<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicApprovalScore extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = [
        'candidate_id',
        'profile_slug',
        'approval_score',
        'source',
        'fetched_at',
    ];

    protected $casts = [
        'approval_score' => 'float',
        'fetched_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
