<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'nick_name', 'phone', 'email', 'position_id', 'political_party_id', 'bloc_id', 'user_id',
        'profile_picture', 'featured', 'approval_status', 'about', 'country', 'county', 'constituency', 'ward'
    ];

    protected $casts = [
        'featured' => 'boolean',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function politicalParty()
    {
        return $this->belongsTo(PoliticalParty::class);
    }

    public function bloc()
    {
        return $this->belongsTo(Bloc::class);
    }

    public function smsSetting(): HasOne
    {
        return $this->hasOne(CandidateSmsSetting::class);
    }

    public function smsMessages(): HasMany
    {
        return $this->hasMany(CandidateSmsMessage::class);
    }
}
