<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bloc extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tribes',
        'tribe_population',
        'voting_patterns',
    ];

    protected $casts = [
        'tribes' => 'array',
        'voting_patterns' => 'array',
        'tribe_population'=> 'integer',
    ];

    // Relationship: One Bloc has many Counties
    public function counties()
    {
        return $this->hasMany(County::class);
    }
}


