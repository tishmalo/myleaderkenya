<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\PermissionRepositoryInterface;
use App\Contracts\Repositories\Admin\RoleRepositoryInterface;
use App\Contracts\Repositories\Admin\UserRepositoryInterface;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

class UserAccessService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private RoleRepositoryInterface $roles,
        private PermissionRepositoryInterface $permissions,
        private UserService $userService,
    ) {}

    /**
     * Assemble every dataset the User Access page renders.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getIndexData(array $filters, ?int $selectedRoleId = null): array
    {
        $roles = $this->roles->allWithPermissionsOrdered();
        $staffRoles = $roles->where('grants_admin_access', true)->values();
        $editableRoles = $roles->where('has_full_access', false)->values();

        // The permissions tab works on one role at a time.
        $selectedRole = $editableRoles->firstWhere('id', $selectedRoleId) ?? $editableRoles->first();

        $permissions = $this->permissions->allGroupedByGroup();
        $admins = $this->users->getAdminUsers();

        // Role Allocation filters (name/username/email search + role filter).
        $search = trim((string) ($filters['search'] ?? ''));
        $roleFilter = (string) ($filters['role_filter'] ?? '');

        if ($roleFilter !== '' && ! $roles->contains('name', $roleFilter)) {
            $roleFilter = '';
        }

        $users = $this->users->getRoleAllocationUsersPaginated([
            'search' => $search,
            'role_filter' => $roleFilter,
        ], 20);

        return compact(
            'admins',
            'editableRoles',
            'permissions',
            'roleFilter',
            'roles',
            'search',
            'selectedRole',
            'staffRoles',
            'users',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{status: string, message: string}
     */
    public function createAdmin(array $data): array
    {
        $data['role_id'] = $data['role_id'] ?? $this->roles->idFor(Role::ADMIN);

        $role = $data['role_id'] !== null ? $this->roles->find((int) $data['role_id']) : null;

        $this->userService->createUser($data);

        return [
            'status' => 'success',
            'message' => ($role?->label ?? 'Admin').' account created successfully.',
        ];
    }

    /**
     * @return array{status: string, message: string}
     */
    public function updateUserRole(User $user, int $roleId, User $actor): array
    {
        $role = $this->roles->findOrFail($roleId);

        if ($actor->is($user) && $user->isSuperAdmin() && ! $role->has_full_access) {
            return ['status' => 'error', 'message' => 'You cannot remove your own super admin access.'];
        }

        if ($user->isSuperAdmin() && ! $role->has_full_access && ! $this->roles->hasAnotherFullAccessUser($user)) {
            return ['status' => 'error', 'message' => 'At least one account must keep full access.'];
        }

        $user->forceFill([
            'role_id' => $role->id,
            'role' => $role->legacyUserRole(),
        ])->save();

        return ['status' => 'success', 'message' => 'User role updated successfully.'];
    }

    /**
     * @param  list<int>  $permissionIds
     * @return array{status: string, message: string}
     */
    public function updatePermissions(Role $role, array $permissionIds): array
    {
        if ($role->has_full_access) {
            return ['status' => 'error', 'message' => $role->label.' keeps all permissions by default.'];
        }

        $this->roles->syncPermissions($role, $permissionIds);

        return ['status' => 'success', 'message' => $role->label.' permissions updated successfully.'];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{status: string, message: string}
     */
    public function createRole(array $data): array
    {
        $role = $this->roles->create([
            'name' => $this->uniqueName($data['name'] ?? null, $data['label']),
            'label' => $data['label'],
            'description' => $data['description'] ?? null,
            'grants_admin_access' => ! empty($data['grants_admin_access']),
            'has_full_access' => false,
            'is_system' => false,
            'sort_order' => $data['sort_order'] ?? 100,
        ]);

        return ['status' => 'success', 'message' => $role->label.' created. Set its permissions next.'];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{status: string, message: string}
     */
    public function updateRoleSettings(Role $role, array $data, User $actor): array
    {
        $grantsAdminAccess = ! empty($data['grants_admin_access']);
        $hasFullAccess = ! empty($data['has_full_access']);

        // A full access role must also be able to reach the admin area.
        if ($hasFullAccess) {
            $grantsAdminAccess = true;
        }

        if ($role->isSystem() && $role->has_full_access && ! $hasFullAccess) {
            return ['status' => 'error', 'message' => $role->label.' must keep full access.'];
        }

        if ($hasFullAccess && ! $role->has_full_access && ! $actor->isSuperAdmin()) {
            return ['status' => 'error', 'message' => 'Only a super admin can grant full access.'];
        }

        if ($role->grants_admin_access && ! $grantsAdminAccess && $this->roles->hasUsers($role)) {
            return ['status' => 'error', 'message' => 'Move the accounts on '.$role->label.' to another role before removing its admin access.'];
        }

        $attributes = [
            'label' => $data['label'],
            'description' => $data['description'] ?? null,
            'grants_admin_access' => $grantsAdminAccess,
            'has_full_access' => $hasFullAccess,
            'sort_order' => $data['sort_order'] ?? $role->sort_order,
        ];

        if (! $role->isSystem()) {
            $attributes['name'] = $this->uniqueName($data['name'] ?? null, $data['label'], $role);
        }

        $role->fill($attributes);
        $this->roles->save($role);

        // Keep the legacy users.role column in step with the role flag.
        $this->roles->updateUsersLegacyRole($role);

        return ['status' => 'success', 'message' => $role->label.' updated successfully.'];
    }

    /**
     * @return array{status: string, message: string}
     */
    public function deleteRole(Role $role): array
    {
        if ($role->isSystem()) {
            return ['status' => 'error', 'message' => $role->label.' is a built in role and cannot be deleted.'];
        }

        if ($this->roles->hasUsers($role)) {
            return ['status' => 'error', 'message' => 'Move the accounts on '.$role->label.' to another role first.'];
        }

        $label = $role->label;

        $this->roles->detachPermissions($role);
        $this->roles->delete($role);

        return ['status' => 'success', 'message' => $label.' deleted.'];
    }

    /**
     * Build a unique, url friendly role name from a label when none is given.
     */
    private function uniqueName(?string $name, string $label, ?Role $ignore = null): string
    {
        $base = Str::slug($name ?: $label, '_') ?: 'role';
        $candidate = $base;
        $suffix = 2;

        while ($this->roles->nameExists($candidate, $ignore)) {
            $candidate = $base.'_'.$suffix++;
        }

        return $candidate;
    }
}
