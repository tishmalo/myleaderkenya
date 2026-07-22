<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const USER = 'user';
    public const ADMIN = 'admin';
    public const SUPERADMIN = 'superadmin';

    protected $fillable = [
        'name',
        'label',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function idFor(string $name): ?int
    {
        return static::query()->where('name', $name)->value('id');
    }
}
