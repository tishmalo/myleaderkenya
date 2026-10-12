<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Validation for the Role Allocation and role management forms lives in the
 * dedicated Form Requests, so the controller only ever sees clean input.
 */
class UserAccessValidationTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'username' => 'root-operator',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);
    }

    public function test_role_assignment_requires_an_existing_role(): void
    {
        $admin = $this->superAdmin();
        $target = User::factory()->create(['username' => 'target-user']);

        $this->actingAs($admin)
            ->from(route('user-access.index'))
            ->patch(route('user-access.roles.update', $target), ['role_id' => 999999])
            ->assertSessionHasErrors('role_id');

        $this->actingAs($admin)
            ->from(route('user-access.index'))
            ->patch(route('user-access.roles.update', $target), [])
            ->assertSessionHasErrors('role_id');
    }

    public function test_role_permissions_must_reference_real_permissions(): void
    {
        $editable = Role::query()->where('name', Role::ADMIN)->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->from(route('user-access.index'))
            ->patch(route('user-access.permissions.update', $editable), ['permissions' => [999999]])
            ->assertSessionHasErrors('permissions.0');

        $this->assertTrue(Permission::query()->exists());
    }

    public function test_role_creation_requires_a_label_and_a_unique_name(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->post(route('user-access.roles.store'), [])
            ->assertSessionHasErrors('label');

        $this->actingAs($admin)
            ->post(route('user-access.roles.store'), ['label' => 'Sneaky', 'name' => Role::ADMIN])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseMissing('roles', ['label' => 'Sneaky']);
    }

    public function test_role_settings_require_a_label(): void
    {
        $role = Role::query()->where('name', Role::ADMIN)->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->patch(route('user-access.roles.settings.update', $role), [])
            ->assertSessionHasErrors('label');
    }
}
