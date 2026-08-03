<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicPulseHomepageCandidate extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = ['candidate_id', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
