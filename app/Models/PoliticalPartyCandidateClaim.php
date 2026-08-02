<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoliticalPartyCandidateClaim extends Model
{
    protected $guarded = [];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function politicalParty()
    {
        return $this->belongsTo(PoliticalParty::class);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
