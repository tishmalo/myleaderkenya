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

    public function bloc()
    {
        return $this->belongsTo(Bloc::class);
    }

    public function blocs()
    {
        return $this->belongsToMany(Bloc::class, 'bloc_county')->withTimestamps();
    }

    public function constituencies()
    {
        return $this->hasMany(Constituency::class);
    }

    public function pollingStations()
    {
        return $this->hasMany(PollingStation::class, 'county', 'name');
    }
}