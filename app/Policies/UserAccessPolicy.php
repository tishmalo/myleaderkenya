<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class UserAccessPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canAccess(Permission::USER_ACCESS_VIEW);
    }

    public function createAdmin(User $user): bool
    {
        return $user->canAccess(Permission::USER_ACCESS_CREATE_ADMIN);
    }

    public function assignRole(User $user): bool
    {
        return $user->canAccess(Permission::USER_ACCESS_ASSIGN_ROLE);
    }

    public function managePermissions(User $user): bool
    {
        return $user->canAccess(Permission::USER_ACCESS_MANAGE_PERMISSIONS);
    }
}
