<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_hash',
        'password',
        'role',
        'username',
        'phone',
        'gender',
        'year_of_birth',
        'county',
        'constituency',
        'ward',
        'polling_station',
        'country_of_residence',
        'is_voter',
        'is_registered',
        'is_aspirant',
        'email_verified_at',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'year_of_birth' => 'integer',
        ];
    }

    public function getEmailAttribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setEmailAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['email'] = null;
            $this->attributes['email_hash'] = null;
            return;
        }

        $email = Str::lower(trim((string) $value));

        $this->attributes['email'] = Crypt::encryptString($email);
        $this->attributes['email_hash'] = hash('sha256', $email);
    }

    public function getPhoneAttribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setPhoneAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['phone'] = null;
            return;
        }

        $phone = trim((string) $value);
        $this->attributes['phone'] = Crypt::encryptString($phone);
    }

    private function decryptNullableString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return (string) $value;
        }
    }

    /**
     * Relationship: One user has one location (linked by username)
     */
    public function location()
    {
        return $this->hasOne(Location::class, 'name', 'username');
    }

    public function getUserTypeAttribute(): string
    {
        if (($this->role ?? null) === 'admin') {
            return 'admin';
        }

        if ((bool) ($this->is_aspirant ?? false)) {
            return 'aspirant';
        }

        if (empty($this->email) && empty($this->phone)) {
            return 'user';
        }

        $hasCandidateProfile = Candidate::query()
            ->where(function ($query) {
                if (!empty($this->email)) {
                    $query->orWhere('email', $this->email);
                }

                if (!empty($this->phone)) {
                    $query->orWhere('phone', $this->phone);
                }
            })
            ->exists();

        return $hasCandidateProfile ? 'aspirant' : 'user';
    }

        // Voter status relationship (optional)
    public function messages()
    {
        return $this->hasMany(Message::class, 'username', 'username');
    }

    public function groups()
{
    return $this->belongsToMany(Group::class, 'group_members')
                ->withTimestamps();
}
}

