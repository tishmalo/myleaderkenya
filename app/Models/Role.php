<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model implements AuditableContract
{
    use AuditsChanges;
    use HasFactory;

    /** The default role every account falls back to. */
    public const USER = 'user';

    /** The day to day staff role. */
    public const ADMIN = 'admin';

    /** The role that bypasses every permission check. */
    public const SUPERADMIN = 'superadmin';

    protected $fillable = [
        'name',
        'label',
        'description',
        'grants_admin_access',
        'has_full_access',
        'is_system',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'grants_admin_access' => 'boolean',
            'has_full_access' => 'boolean',
            'is_system' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->relationLoaded('permissions')) {
            return $this->permissions->contains('name', $permission);
        }

        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Roles in the order they should be shown in pickers and lists.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    /**
     * Roles whose members may sign in to the admin area.
     */
    public function scopeStaff(Builder $query): Builder
    {
        return $query->where('grants_admin_access', true);
    }

    /**
     * Roles that ignore every permission check.
     */
    public function scopeFullAccess(Builder $query): Builder
    {
        return $query->where('has_full_access', true);
    }

    /**
     * The value stored in the legacy users.role column for this role.
     *
     * That column predates the roles table and is still read by older queries,
     * so keep writing "admin" for everyone who can reach the admin area.
     */
    public function legacyUserRole(): string
    {
        return $this->grants_admin_access ? self::ADMIN : self::USER;
    }

    public function isSystem(): bool
    {
        return (bool) $this->is_system;
    }

    public static function idFor(string $name): ?int
    {
        return static::query()->where('name', $name)->value('id');
    }
}
