<?php

namespace Tests\Feature;

use App\Models\Coalition;
use App\Models\Permission;
use App\Models\PoliticalParty;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Managing coalitions and political parties must follow the permission a
 * person actually holds. A clerk allowed to edit a coalition but not to browse
 * the party list used to get a bare 403 on the edit screen, because the whole
 * resource was gated by the view permission.
 */
class CoalitionAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private ?User $superAdmin = null;

    private function superAdmin(): User
    {
        return $this->superAdmin ??= User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);
    }

    /**
     * A staff role holding exactly the given permissions.
     *
     * @param  list<string>  $permissionNames
     */
    private function staffWith(array $permissionNames, string $roleName = 'party_clerk'): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            [
                'label' => 'Party Clerk',
                'description' => 'Maintains coalitions and parties',
                'grants_admin_access' => true,
                'has_full_access' => false,
                'is_system' => false,
                'sort_order' => 60,
            ]
        );

        $role->permissions()->sync(
            Permission::query()->whereIn('name', $permissionNames)->pluck('id')->all()
        );

        return User::factory()->create([
            'name' => 'Clerk '.$roleName,
            'username' => 'clerk-'.$roleName,
            'role' => 'admin',
            'role_id' => $role->id,
        ]);
    }

    private function coalition(string $name = 'Jubilee Coalition'): Coalition
    {
        return Coalition::create([
            'name' => $name,
            'content' => 'A coalition of parties.',
            'status' => 'published',
        ]);
    }

    private function party(string $name = 'Jubilee Party'): PoliticalParty
    {
        return PoliticalParty::create([
            'name' => $name,
            'abbreviation' => strtoupper(substr(md5($name), 0, 3)),
            'content' => 'A political party.',
            'status' => 'published',
        ]);
    }

    public function test_a_super_admin_can_open_every_coalition_screen(): void
    {
        $coalition = $this->coalition();

        $this->actingAs($this->superAdmin())
            ->get(route('coalitions.index'))
            ->assertOk();

        $this->actingAs($this->superAdmin())
            ->get(route('coalitions.create'))
            ->assertOk();

        $this->actingAs($this->superAdmin())
            ->get(route('coalitions.edit', $coalition))
            ->assertOk();
    }

    public function test_an_editor_without_view_permission_can_still_edit_a_coalition(): void
    {
        $editor = $this->staffWith(['parties.update']);
        $coalition = $this->coalition();

        // The edit screen and its save action work on the update permission
        // alone - the list is a different capability.
        $this->actingAs($editor)
            ->get(route('coalitions.edit', $coalition))
            ->assertOk();

        $this->actingAs($editor)
            ->put(route('coalitions.update', $coalition), [
                'name' => 'Jubilee Coalition Renamed',
                'content' => 'Updated content.',
                'status' => 'published',
            ])
            ->assertRedirect(route('coalitions.index'));

        $this->assertSame('Jubilee Coalition Renamed', $coalition->fresh()->name);

        $this->actingAs($editor)->get(route('coalitions.index'))->assertForbidden();
        $this->actingAs($editor)->get(route('coalitions.create'))->assertForbidden();
    }

    public function test_a_clerk_with_create_permission_can_add_a_coalition(): void
    {
        $clerk = $this->staffWith(['parties.create'], 'party_creator');

        $this->actingAs($clerk)->get(route('coalitions.create'))->assertOk();

        $this->actingAs($clerk)
            ->post(route('coalitions.store'), [
                'name' => 'Azimio Coalition',
                'content' => 'A new coalition.',
                'status' => 'draft',
            ])
            ->assertRedirect(route('coalitions.index'));

        $this->assertDatabaseHas('coalitions', ['name' => 'Azimio Coalition']);

        // Creating does not imply deleting.
        $coalition = Coalition::query()->where('name', 'Azimio Coalition')->firstOrFail();

        $this->actingAs($clerk)
            ->delete(route('coalitions.destroy', $coalition))
            ->assertForbidden();
    }

    public function test_a_deleter_without_view_permission_can_remove_a_coalition(): void
    {
        $remover = $this->staffWith(['parties.delete'], 'party_remover');
        $coalition = $this->coalition();

        $this->actingAs($remover)
            ->deleteJson(route('coalitions.destroy', $coalition))
            ->assertOk();

        $this->assertDatabaseMissing('coalitions', ['id' => $coalition->id]);
    }

    public function test_staff_without_any_party_permission_is_forbidden_rather_than_crashing(): void
    {
        $outsider = $this->staffWith(['news.view'], 'news_desk');
        $coalition = $this->coalition();

        $this->actingAs($outsider)->get(route('coalitions.index'))->assertForbidden();
        $this->actingAs($outsider)->get(route('coalitions.create'))->assertForbidden();
        $this->actingAs($outsider)->get(route('coalitions.edit', $coalition))->assertForbidden();

        $this->actingAs($outsider)
            ->put(route('coalitions.update', $coalition), [
                'name' => 'Hijacked Coalition',
                'content' => 'Not mine to edit.',
                'status' => 'draft',
            ])
            ->assertForbidden();

        $this->assertSame('Jubilee Coalition', $coalition->fresh()->name);
    }

    public function test_political_parties_follow_the_same_per_action_permissions(): void
    {
        $editor = $this->staffWith(['parties.update'], 'party_editor');
        $party = $this->party();

        $this->actingAs($this->superAdmin())
            ->get(route('political-parties.index'))
            ->assertOk();

        $this->actingAs($editor)
            ->get(route('political-parties.edit', $party))
            ->assertOk();

        $this->actingAs($editor)->get(route('political-parties.index'))->assertForbidden();
        $this->actingAs($editor)->get(route('political-parties.create'))->assertForbidden();
    }

    public function test_a_role_row_without_the_access_flags_still_reaches_the_admin_area(): void
    {
        Role::query()->where('name', Role::ADMIN)->firstOrFail()->permissions()->syncWithoutDetaching(
            Permission::query()->where('name', 'parties.view')->pluck('id')->all()
        );

        // Simulates a deploy where the roles flag migration has not run yet:
        // the flags are simply not there, so the role name has to carry the
        // decision instead of locking every staff member out.
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['grants_admin_access', 'has_full_access']);
        });

        $superAdmin = User::factory()->create([
            'name' => 'Legacy Root',
            'username' => 'legacy-root',
            'role' => 'superadmin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);

        $clerk = User::factory()->create([
            'name' => 'Legacy Clerk',
            'username' => 'legacy-clerk',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::ADMIN),
        ]);

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertTrue($superAdmin->isAdmin());
        $this->assertTrue($superAdmin->canAccess('parties.view'));

        $this->assertTrue($clerk->isAdmin());
        $this->assertTrue($clerk->canAccess('parties.view'));

        $this->actingAs($superAdmin)->get(route('coalitions.index'))->assertOk();
        $this->actingAs($clerk)->get(route('coalitions.index'))->assertOk();
    }
}
