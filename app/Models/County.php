<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class County extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bloc_id',
        'area',
        'population',
        'capital',
        'registered_voters',
        'postal_abbreviation',
    ];

    // Relationship: County belongs to one Bloc
    public function bloc()
    {
        return $this->belongsTo(Bloc::class);
    }

    // Relationship: County has many Constituencies
    public function constituencies()
    {
        return $this->hasMany(Constituency::class);
    }

    public function pollingStations()
{
    return $this->hasMany(PollingStation::class, 'county', 'name');   // 'county' column in polling_stations = 'name' in counties
}

//     public function pollingStations()
// {
//     return $this->hasMany(\App\Models\PollingStation::class);
// }
}