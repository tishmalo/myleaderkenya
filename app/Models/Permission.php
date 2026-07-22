<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    public const USER_ACCESS_VIEW = 'user-access.view';
    public const USER_ACCESS_CREATE_ADMIN = 'user-access.create-admin';
    public const USER_ACCESS_ASSIGN_ROLE = 'user-access.assign-role';
    public const USER_ACCESS_MANAGE_PERMISSIONS = 'user-access.manage-permissions';

    protected $fillable = [
        'name',
        'label',
        'group',
        'sort_order',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
}
