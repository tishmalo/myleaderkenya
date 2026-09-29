<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PollOption extends Model implements AuditableContract
{
    use AuditsChanges;

    protected $fillable = [
        'poll_id',
        'label',
        'candidate_id',
        'display_order',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }
}
