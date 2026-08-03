<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;

class PoliticalPartyTokenWallet extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $guarded = [];

    public function politicalParty()
    {
        return $this->belongsTo(PoliticalParty::class);
    }

    public function transactions()
    {
        return $this->hasMany(PoliticalPartyTokenTransaction::class);
    }
}
