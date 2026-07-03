<?php

namespace App\Models;

use App\Models\Concerns\EncryptsPiiAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Candidate extends Model
{
    use EncryptsPiiAttributes, HasFactory;

    protected $fillable = [
        'name', 'nick_name', 'phone', 'email', 'position_id', 'political_party_id', 'bloc_id',
        'profile_picture', 'featured', 'about', 'country', 'county', 'constituency', 'ward'
    ];

    protected $casts = [
        'featured' => 'boolean',
    ];

    protected $hidden = [
        'email_hash',
        'phone_hash',
    ];

    public function getEmailAttribute($value): ?string
    {
        return $this->decryptPiiValue($value);
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $this->encryptPiiValue($value);

        if (Schema::hasColumn($this->getTable(), 'email_hash')) {
            $this->attributes['email_hash'] = static::piiHash($value);
        }
    }

    public function getPhoneAttribute($value): ?string
    {
        return $this->decryptPiiValue($value);
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $this->encryptPiiValue($value);

        if (Schema::hasColumn($this->getTable(), 'phone_hash')) {
            $this->attributes['phone_hash'] = static::piiHash($value);
        }
    }

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
}
