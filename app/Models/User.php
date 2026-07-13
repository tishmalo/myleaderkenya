<?php

namespace App\Models;

use App\Models\Concerns\EncryptsPiiAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use EncryptsPiiAttributes, HasApiTokens, HasFactory, Notifiable;

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
        'id_number',
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
        'email_hash',
        'phone_hash',
        'id_number_hash',
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
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return (string) $value;
        }
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

    /**
     * Relationship: One user has one location (linked by username)
     */
    public function location()
    {
        return $this->hasOne(Location::class, 'name', 'username');
    }

    public static function findByEmailValue(string $email): ?self
    {
        if (static::piiHash($email) === null) {
            return null;
        }

        $query = static::query();

        if (Schema::hasColumn('users', 'email_hash')) {
            return $query->where('email_hash', static::piiHash($email))->first();
        }

        return $query->where('email', $email)->first();
    }

    public static function emailExists(string $email, ?int $ignoreUserId = null): bool
    {
        if (static::piiHash($email) === null) {
            return false;
        }

        $query = static::query();

        if (Schema::hasColumn('users', 'email_hash')) {
            $query->where('email_hash', static::piiHash($email));
        } else {
            $query->where('email', $email);
        }

        if ($ignoreUserId) {
            $query->whereKeyNot($ignoreUserId);
        }

        return $query->exists();
    }

    public static function idNumberExists(string $idNumber, ?int $ignoreUserId = null): bool
    {
        if (static::piiHash($idNumber) === null) {
            return false;
        }

        $query = static::query();

        if (Schema::hasColumn('users', 'id_number_hash')) {
            $query->where('id_number_hash', static::piiHash($idNumber));
        } else {
            $query->where('id_number', $idNumber);
        }

        if ($ignoreUserId) {
            $query->whereKeyNot($ignoreUserId);
        }

        return $query->exists();
    }

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

    public function getIdNumberAttribute($value): ?string
    {
        return $this->decryptPiiValue($value);
    }

    public function setIdNumberAttribute($value): void
    {
        $this->attributes['id_number'] = $this->encryptPiiValue($value);

        if (Schema::hasColumn($this->getTable(), 'id_number_hash')) {
            $this->attributes['id_number_hash'] = static::piiHash($value);
        }
    }

    public function getUserTypeAttribute(): string
    {
        if (($this->role ?? null) === 'admin') {
            return 'admin';
        }

        return $this->candidateProfile() ? 'aspirant' : 'user';
    }

    public function candidateProfile(): ?Candidate
    {
        if (empty($this->email) && empty($this->phone)) {
            return null;
        }

        return Candidate::query()
            ->where(function ($query) {
                if (!empty($this->email)) {
                    if (Schema::hasColumn('candidates', 'email_hash')) {
                        $query->orWhere('email_hash', Candidate::piiHash($this->email));
                    } else {
                        $query->orWhere('email', $this->email);
                    }
                }

                if (!empty($this->phone)) {
                    if (Schema::hasColumn('candidates', 'phone_hash')) {
                        $query->orWhere('phone_hash', Candidate::piiHash($this->phone));
                    } else {
                        $query->orWhere('phone', $this->phone);
                    }
                }
            })
            ->with(['position', 'politicalParty'])
            ->latest()
            ->first();
    }

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

