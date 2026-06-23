<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'message',
        'latitude',
        'longitude',
        'country',
        'county',
        'constituency',
        'ward',
        'quoted_message_id',
        'tag_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    /**
     * New scope: Get messages for a specific constituency chatroom
     */
    public function scopeInConstituency($query, $county, $constituency)
    {
        return $query->where('county', $county)
                     ->where('constituency', $constituency)
                     ->orderBy('created_at', 'desc');
    }

    /**
     * Find nearby messages within a radius (in meters)
     * Using Haversine formula with proper parameter binding
     */
    public function scopeNearby($query, $lat, $lng, $radius = 500)
    {
        // Validate and cast inputs to prevent SQL injection
        $lat = (float) $lat;
        $lng = (float) $lng;
        $radius = (int) $radius;

        return $query->selectRaw("*,
            (6371000 * acos(
                cos(radians(?)) *
                cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(latitude))
            )) AS distance", [$lat, $lng, $lat])
            ->havingRaw('distance <= ?', [$radius])
            ->orderBy('distance');
    }

    public function quotedMessage()
    {
        return $this->belongsTo(Message::class, 'quoted_message_id');
    }

    public function scopeInLocation($query, $level, $name)
    {
        return match ($level) {
            'country'       => $query->where('country', $name),
            'county'        => $query->where('county', $name),
            'constituency'  => $query->where('constituency', $name),
            'ward'          => $query->where('ward', $name),
            default         => $query,
        };
    }

public function tag()
{
    return $this->belongsTo(Tag::class);
}

public function reactions()
{
    return $this->hasMany(MessageReaction::class);
}


}