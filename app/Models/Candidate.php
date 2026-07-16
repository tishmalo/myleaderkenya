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
        'profile_picture', 'cover_photo', 'featured', 'approval_status', 'about', 'country', 'county', 'constituency', 'ward',
        'claim_token_hash', 'claim_token_expires_at', 'claim_sent_at', 'claimed_at',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'claim_token_expires_at' => 'datetime',
        'claim_sent_at' => 'datetime',
        'claimed_at' => 'datetime',
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



