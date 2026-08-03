<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;

class PoliticalPartyTokenPurchase extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $guarded = [];

    protected $casts = ['gateway_response' => 'array', 'callback_received_at' => 'datetime', 'credited_at' => 'datetime'];

    public function politicalParty()
    {
        return $this->belongsTo(PoliticalParty::class);
    }

    public function package()
    {
        return $this->belongsTo(CandidateTokenPackage::class, 'candidate_token_package_id');
    }
}
