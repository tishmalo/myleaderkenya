<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;

class PoliticalPartyTokenTransaction extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $guarded = [];

    protected $casts = ['metadata' => 'array', 'finalized_at' => 'datetime'];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
