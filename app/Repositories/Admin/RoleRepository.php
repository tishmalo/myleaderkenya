<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\RoleRepositoryInterface;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function allWithPermissionsOrdered(): Collection
    {
        return Role::query()->with('permissions')->ordered()->get();
    }

    public function find(int $id): ?Role
    {
        return Role::query()->find($id);
    }

    public function findOrFail(int $id): Role
    {
        return Role::query()->findOrFail($id);
    }

    public function idFor(string $name): ?int
    {
        return Role::query()->where('name', $name)->value('id');
    }

    public function create(array $attributes): Role
    {
        return Role::query()->create($attributes);
    }

    public function save(Role $role): bool
    {
        return $role->save();
    }

    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    public function hasUsers(Role $role): bool
    {
        return $role->users()->exists();
    }

    public function nameExists(string $name, ?Role $ignore = null): bool
    {
        return Role::query()
            ->where('name', $name)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists();
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }

    public function detachPermissions(Role $role): void
    {
        $role->permissions()->detach();
    }

    public function updateUsersLegacyRole(Role $role): void
    {
        $role->users()->update(['role' => $role->legacyUserRole()]);
    }

    public function hasAnotherFullAccessUser(User $except): bool
    {
        return User::query()
            ->whereKeyNot($except->getKey())
            ->whereHas('role', fn ($roleQuery) => $roleQuery->where('has_full_access', true))
            ->exists();
    }
}
