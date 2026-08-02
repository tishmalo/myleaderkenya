<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoliticalPartyTokenWallet extends Model
{
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
