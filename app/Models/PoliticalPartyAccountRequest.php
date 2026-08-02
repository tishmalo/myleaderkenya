<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoliticalPartyAccountRequest extends Model
{
    protected $guarded = [];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function politicalParty()
    {
        return $this->belongsTo(PoliticalParty::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
