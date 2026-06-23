<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'constituency_id',
        'population',
        'registered_voters',
    ];

    // Relationship: Ward belongs to one Constituency
    public function constituency()
    {
        return $this->belongsTo(Constituency::class);
    }

    public function pollingStations()
{
    return $this->hasMany(PollingStation::class, 'ward', 'name');
}
//     public function pollingStations()
// {
//     return $this->hasMany(\App\Models\PollingStation::class);
// }
}