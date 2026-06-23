<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;   // ← This is the important line

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
        'password',
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

    /**
     * Relationship: One user has one location (linked by username)
     */
    public function location()
    {
        return $this->hasOne(Location::class, 'name', 'username');
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
