<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AspirantPoll extends Model
{
    protected $fillable = [
        'candidate_id',
        'user_id',
        'group_id',
        'question',
        'options',
        'scope_type',
        'scope_column',
        'scope_value',
        'status',
        'published_at',
    ];

    protected $casts = [
        'options' => 'array',
        'published_at' => 'datetime',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function responses()
    {
        return $this->hasMany(AspirantPollResponse::class);
    }
}
