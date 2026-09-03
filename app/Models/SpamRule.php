<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpamRule extends Model
{
    public const TYPES = ['keyword', 'domain', 'email', 'phone', 'ip', 'regex'];

    protected $fillable = ['type', 'value', 'enabled', 'source', 'created_by'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}