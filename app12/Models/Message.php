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
     * Keep your old nearby scope if you still want it
     */
    public function scopeNearby($query, $lat, $lng, $radius = 500)
    {
        return $query->selectRaw("*, 
            (6371000 * acos(
                cos(radians(?)) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * 
                sin(radians(latitude))
            )) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<=', $radius)
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