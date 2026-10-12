<?php

namespace Tests\Unit;

use App\Contracts\Repositories\Admin\PermissionRepositoryInterface;
use App\Contracts\Repositories\Admin\RoleRepositoryInterface;
use App\Contracts\Repositories\Admin\UserRepositoryInterface;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\UserAccessService;
use App\Services\Admin\UserService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;
use Tests\TestCase;

class UserAccessServiceTest extends TestCase
{
    private function service(
        ?UserRepositoryInterface $users = null,
        ?RoleRepositoryInterface $roles = null,
        ?PermissionRepositoryInterface $permissions = null,
        ?UserService $userService = null,
    ): UserAccessService {
        return new UserAccessService(
            $users ?? $this->createMock(UserRepositoryInterface::class),
            $roles ?? $this->createMock(RoleRepositoryInterface::class),
            $permissions ?? $this->createMock(PermissionRepositoryInterface::class),
            $userService ?? $this->createMock(UserService::class),
        );
    }

    public function test_get_index_data_rejects_unknown_role_filter(): void
    {
        $role = new Role(['name' => 'admin', 'label' => 'Admin', 'grants_admin_access' => true]);

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->method('allWithPermissionsOrdered')->willReturn(new Collection([$role]));

        $permissions = $this->createMock(PermissionRepositoryInterface::class);
        $permissions->method('allGroupedByGroup')->willReturn(new SupportCollection);

        $users = $this->createMock(UserRepositoryInterface::class);
        $users->method('getAdminUsers')->willReturn(new Collection);
        $users->expects($this->once())
            ->method('getRoleAllocationUsersPaginated')
            ->with($this->callback(fn (array $filters) => $filters['role_filter'] === ''), 20)
            ->willReturn(new LengthAwarePaginator([], 0, 20));

        $data = $this->service($users, $roles, $permissions)->getIndexData([
            'search' => '  Ada  ',
            'role_filter' => 'ghost',
        ]);

        $this->assertSame('Ada', $data['search']);
        $this->assertSame('', $data['roleFilter']);
        $this->assertSame($role, $data['selectedRole']);
    }

    public function test_create_admin_defaults_to_the_admin_role(): void
    {
        $adminRole = new Role(['name' => 'admin', 'label' => 'Administrator', 'grants_admin_access' => true]);

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->method('idFor')->with(Role::ADMIN)->willReturn(7);
        $roles->method('find')->with(7)->willReturn($adminRole);

        $userService = $this->createMock(UserService::class);
        $userService->expects($this->once())
            ->method('createUser')
            ->with($this->callback(fn (array $data) => $data['role_id'] === 7))
            ->willReturn(new User);

        $result = $this->service(roles: $roles, userService: $userService)
            ->createAdmin(['username' => 'clerk', 'name' => 'Clerk', 'password' => 'secret']);

        $this->assertSame('success', $result['status']);
        $this->assertSame('Administrator account created successfully.', $result['message']);
    }

    public function test_update_user_role_blocks_removing_your_own_super_admin_access(): void
    {
        $user = new User(['role' => Role::SUPERADMIN]);

        $targetRole = new Role(['name' => Role::USER, 'label' => 'User', 'has_full_access' => false]);
        $targetRole->id = 3;

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->method('findOrFail')->willReturn($targetRole);

        $result = $this->service(roles: $roles)->updateUserRole($user, 3, $user);

        $this->assertSame('error', $result['status']);
        $this->assertSame('You cannot remove your own super admin access.', $result['message']);
    }

    public function test_update_user_role_protects_the_last_full_access_account(): void
    {
        $actor = new User(['role' => 'admin']);
        $actor->id = 1;
        $user = new User(['role' => Role::SUPERADMIN]);
        $user->id = 2;

        $targetRole = new Role(['name' => Role::USER, 'label' => 'User', 'has_full_access' => false]);
        $targetRole->id = 3;

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->method('findOrFail')->willReturn($targetRole);
        $roles->expects($this->once())->method('hasAnotherFullAccessUser')->willReturn(false);

        $result = $this->service(roles: $roles)->updateUserRole($user, 3, $actor);

        $this->assertSame('error', $result['status']);
        $this->assertSame('At least one account must keep full access.', $result['message']);
    }

    public function test_update_user_role_persists_the_new_role_when_allowed(): void
    {
        $actor = new User(['role' => 'admin']);

        $user = new class(['role' => 'user']) extends User
        {
            public bool $saved = false;

            public function save(array $options = []): bool
            {
                $this->saved = true;

                return true;
            }
        };

        $targetRole = new Role(['name' => Role::ADMIN, 'label' => 'Admin', 'grants_admin_access' => true, 'has_full_access' => false]);
        $targetRole->id = 4;

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->method('findOrFail')->willReturn($targetRole);
        $roles->method('hasAnotherFullAccessUser')->willReturn(true);

        $result = $this->service(roles: $roles)->updateUserRole($user, 4, $actor);

        $this->assertSame('success', $result['status']);
        $this->assertTrue($user->saved);
        $this->assertSame(4, $user->role_id);
        $this->assertSame('admin', $user->role);
    }

    public function test_update_permissions_rejects_a_full_access_role(): void
    {
        $role = new Role(['label' => 'Super Admin', 'has_full_access' => true]);

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->expects($this->never())->method('syncPermissions');

        $result = $this->service(roles: $roles)->updatePermissions($role, [1, 2]);

        $this->assertSame('error', $result['status']);
        $this->assertSame('Super Admin keeps all permissions by default.', $result['message']);
    }

    public function test_update_permissions_syncs_an_editable_role(): void
    {
        $role = new Role(['label' => 'Editor', 'has_full_access' => false]);

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->expects($this->once())->method('syncPermissions')->with($role, [1, 2]);

        $result = $this->service(roles: $roles)->updatePermissions($role, [1, 2]);

        $this->assertSame('success', $result['status']);
        $this->assertSame('Editor permissions updated successfully.', $result['message']);
    }

    public function test_create_role_generates_a_unique_name(): void
    {
        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->method('nameExists')->willReturnOnConsecutiveCalls(true, false);
        $roles->expects($this->once())
            ->method('create')
            ->with($this->callback(fn (array $attributes) => $attributes['name'] === 'content_editor_2'))
            ->willReturnCallback(fn (array $attributes) => new Role($attributes));

        $result = $this->service(roles: $roles)->createRole([
            'label' => 'Content Editor',
            'grants_admin_access' => '1',
        ]);

        $this->assertSame('success', $result['status']);
        $this->assertSame('Content Editor created. Set its permissions next.', $result['message']);
    }

    public function test_update_role_settings_protects_a_system_full_access_role(): void
    {
        $actor = new User(['role' => 'admin']);
        $role = new Role(['label' => 'Super Admin', 'is_system' => true, 'has_full_access' => true, 'grants_admin_access' => true]);

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->expects($this->never())->method('save');

        $result = $this->service(roles: $roles)->updateRoleSettings($role, ['label' => 'Super Admin'], $actor);

        $this->assertSame('error', $result['status']);
        $this->assertSame('Super Admin must keep full access.', $result['message']);
    }

    public function test_update_role_settings_only_super_admin_can_grant_full_access(): void
    {
        $actor = new User(['role' => 'admin']);
        $role = new Role(['label' => 'Editor', 'is_system' => false, 'has_full_access' => false, 'grants_admin_access' => false]);

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->expects($this->never())->method('save');

        $result = $this->service(roles: $roles)
            ->updateRoleSettings($role, ['label' => 'Editor', 'has_full_access' => '1'], $actor);

        $this->assertSame('error', $result['status']);
        $this->assertSame('Only a super admin can grant full access.', $result['message']);
    }

    public function test_update_role_settings_blocks_removing_admin_access_with_accounts(): void
    {
        $actor = new User(['role' => Role::SUPERADMIN]);
        $role = new Role(['label' => 'Editor', 'is_system' => false, 'has_full_access' => false, 'grants_admin_access' => true]);

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->method('hasUsers')->willReturn(true);
        $roles->expects($this->never())->method('save');

        $result = $this->service(roles: $roles)
            ->updateRoleSettings($role, ['label' => 'Editor'], $actor);

        $this->assertSame('error', $result['status']);
        $this->assertSame('Move the accounts on Editor to another role before removing its admin access.', $result['message']);
    }

    public function test_update_role_settings_saves_and_syncs_the_legacy_column(): void
    {
        $actor = new User(['role' => Role::SUPERADMIN]);
        $role = new Role(['label' => 'Editor', 'name' => 'editor', 'is_system' => false, 'has_full_access' => false, 'grants_admin_access' => false]);

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->method('nameExists')->willReturn(false);
        $roles->expects($this->once())->method('save')->with($role)->willReturn(true);
        $roles->expects($this->once())->method('updateUsersLegacyRole')->with($role);

        $result = $this->service(roles: $roles)->updateRoleSettings($role, [
            'label' => 'Editor',
            'name' => 'editor',
            'grants_admin_access' => '1',
        ], $actor);

        $this->assertSame('success', $result['status']);
        $this->assertTrue($role->grants_admin_access);
        $this->assertSame('Editor updated successfully.', $result['message']);
    }

    public function test_delete_role_protects_built_in_roles(): void
    {
        $role = new Role(['label' => 'Admin', 'is_system' => true]);

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->expects($this->never())->method('delete');

        $result = $this->service(roles: $roles)->deleteRole($role);

        $this->assertSame('error', $result['status']);
        $this->assertSame('Admin is a built in role and cannot be deleted.', $result['message']);
    }

    public function test_delete_role_protects_roles_with_accounts(): void
    {
        $role = new Role(['label' => 'Editor', 'is_system' => false]);

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->method('hasUsers')->willReturn(true);
        $roles->expects($this->never())->method('delete');

        $result = $this->service(roles: $roles)->deleteRole($role);

        $this->assertSame('error', $result['status']);
        $this->assertSame('Move the accounts on Editor to another role first.', $result['message']);
    }

    public function test_delete_role_detaches_permissions_and_deletes(): void
    {
        $role = new Role(['label' => 'Editor', 'is_system' => false]);

        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->method('hasUsers')->willReturn(false);
        $roles->expects($this->once())->method('detachPermissions')->with($role);
        $roles->expects($this->once())->method('delete')->with($role)->willReturn(true);

        $result = $this->service(roles: $roles)->deleteRole($role);

        $this->assertSame('success', $result['status']);
        $this->assertSame('Editor deleted.', $result['message']);
    }
}
