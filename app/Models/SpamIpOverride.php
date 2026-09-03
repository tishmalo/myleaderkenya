<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpamIpOverride extends Model
{
    protected $fillable = ['ip', 'action', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }
}