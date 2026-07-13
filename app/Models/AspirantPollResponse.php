<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AspirantPollResponse extends Model
{
    protected $fillable = [
        'aspirant_poll_id',
        'user_id',
        'option_index',
    ];

    public function poll()
    {
        return $this->belongsTo(AspirantPoll::class, 'aspirant_poll_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
