<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;

class PoliticalPartyTokenTransfer extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $guarded = [];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function politicalParty()
    {
        return $this->belongsTo(PoliticalParty::class);
    }
}
