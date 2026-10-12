<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    public function allWithPermissionsOrdered(): Collection;

    public function find(int $id): ?Role;

    public function findOrFail(int $id): Role;

    public function idFor(string $name): ?int;

    public function create(array $attributes): Role;

    public function save(Role $role): bool;

    public function delete(Role $role): bool;

    public function hasUsers(Role $role): bool;

    public function nameExists(string $name, ?Role $ignore = null): bool;

    public function syncPermissions(Role $role, array $permissionIds): void;

    public function detachPermissions(Role $role): void;

    public function updateUsersLegacyRole(Role $role): void;

    public function hasAnotherFullAccessUser(User $except): bool;
}
