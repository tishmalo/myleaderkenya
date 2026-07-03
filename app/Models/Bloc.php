<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bloc extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'total_population',
        'total_registered_voters',
        'tribes',
        'tribe_population',
        'voting_patterns',
    ];

    protected $casts = [
        'tribes' => 'array',
        'voting_patterns' => 'array',
        'tribe_population' => 'integer',
        'total_population' => 'integer',
        'total_registered_voters' => 'integer',
    ];

    public function counties()
    {
        return $this->belongsToMany(County::class, 'bloc_county')->withTimestamps();
    }

    public function primaryCounties()
    {
        return $this->hasMany(County::class);
    }
}