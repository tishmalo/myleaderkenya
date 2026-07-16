<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Constituency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'county_id',
        'population',
        'registered_voters',
        'number_of_seats',
    
        'position_name',
        'image',
    ];

    // Relationship: Constituency belongs to one County
    public function county()
    {
        return $this->belongsTo(County::class);
    }

    // Relationship: Constituency has many Wards
    public function wards()
    {
        return $this->hasMany(Ward::class);
    }

    public function pollingStations()
{
    return $this->hasMany(PollingStation::class, 'constituency', 'name');
}
}

