<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollingStation extends Model
{
    use HasFactory;

    protected $fillable = [
        'bloc_id',           // new
        'county',
        'constituency',
        'ward', 
        'office',
        'near_landmark',
        'distance_to_office',
        'lat',
        'lon',
        'registered_voters',
        'is_user_added',
    ];

    protected $casts = [
        'lat'                => 'decimal:8',
        'lon'                => 'decimal:8',
        'registered_voters'  => 'integer',
        'distance_to_office' => 'integer',
        'is_user_added'      => 'boolean',
    ];

    // Optional: relationships (if you create foreign keys later)
    // public function bloc() { return $this->belongsTo(Bloc::class); }
    // Add this method
public function bloc()
{
    return $this->belongsTo(Bloc::class);
}
}