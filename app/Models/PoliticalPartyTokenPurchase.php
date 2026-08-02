<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoliticalPartyTokenPurchase extends Model
{
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
