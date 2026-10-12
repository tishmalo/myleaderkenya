<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Web\DashboardDestinationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Roles must be data, not code: a super admin creates a role in the admin UI,
 * decides whether it can sign in to the admin area, ticks its permissions and
 * every part of the system follows - no constant, middleware or deployment in
 * between.
 */
class RoleManagementTest extends TestCase
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

    private function createRole(array $overrides = []): Role
    {
        $this->actingAs($this->superAdmin())
            ->post(route('user-access.roles.store'), array_merge([
                'label' => 'Content Editor',
                'name' => 'content_editor',
                'description' => 'Runs the news desk',
                'grants_admin_access' => '1',
                'sort_order' => 40,
            ], $overrides))
            ->assertRedirect(route('user-access.index').'#roles');

        return Role::query()->where('name', $overrides['name'] ?? 'content_editor')->firstOrFail();
    }

    private function member(Role $role, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            // A completed profile keeps the profile.complete gate out of the way.
            'gender' => 'female',
            'year_of_birth' => 1990,
            'county' => 'Nyandarua',
            'constituency' => 'Mathi East',
            'ward' => 'Kaguru',
            'country_of_residence' => 'Kenya',
            'role' => $role->legacyUserRole(),
            'role_id' => $role->id,
        ], $attributes));
    }

    /**
     * The markup of a single tab panel, so assertions are not confused by the
     * other panels that legitimately mention the same roles.
     */
    private function panel(TestResponse $response, string $panel): string
    {
        $html = (string) $response->getContent();
        $start = strpos($html, 'data-tab-panel="'.$panel.'"');

        $this->assertNotFalse($start, "The {$panel} panel is missing from the page.");

        $markup = substr($html, (int) $start);
        $end = strpos($markup, '</section>');

        return $end === false ? $markup : substr($markup, 0, $end);
    }

    public function test_a_new_role_is_usable_without_any_code_change(): void
    {
        $role = $this->createRole();

        $this->assertSame('content_editor', $role->name);
        $this->assertSame('Content Editor', $role->label);
        $this->assertTrue($role->grants_admin_access);
        $this->assertFalse($role->has_full_access);
        $this->assertFalse($role->isSystem());

        // It shows up in the role pickers straight away.
        $page = $this->actingAs($this->superAdmin())->get(route('user-access.index'))->assertOk();

        $page->assertSee('Content Editor');
        $page->assertSee('value="'.$role->id.'"', false);
        $page->assertSee('content_editor');
    }

    public function test_permissions_of_a_new_role_are_enforced(): void
    {
        $role = $this->createRole(['grants_admin_access' => '1']);

        $this->actingAs($this->superAdmin())
            ->patch(route('user-access.permissions.update', $role), [
                'permissions' => Permission::query()->whereIn('name', ['news.view', 'news.create'])->pluck('id')->all(),
            ])
            ->assertRedirect(route('user-access.index', ['role' => $role->id]).'#permissions');

        $editor = $this->member($role, ['name' => 'Edna Editor', 'username' => 'edna-editor']);

        $this->assertTrue($editor->fresh()->isAdmin());

        $this->actingAs($editor)->get(route('news.index'))->assertOk();
        $this->actingAs($editor->fresh())->get(route('news.create'))->assertOk();

        // The dashboard needs dashboard.view, which this role does not have, so
        // signing in lands on the first page the role may open.
        $this->assertSame(
            route('news.index'),
            app(DashboardDestinationService::class)->urlFor($editor->fresh())
        );

        $article = NewsArticle::create([
            'title' => 'County results',
            'slug' => 'county-results',
            'content' => 'Body',
            'author_id' => $editor->id,
        ]);

        // Everything outside the granted permissions stays closed.
        $this->actingAs($editor->fresh())->get(route('dashboard'))->assertForbidden();
        $this->actingAs($editor->fresh())->get(route('polls.index'))->assertForbidden();
        $this->actingAs($editor->fresh())->get(route('links.index'))->assertForbidden();
        $this->actingAs($editor->fresh())->get(route('user-access.index'))->assertForbidden();
        $this->actingAs($editor->fresh())->put(route('news.update', $article), [])->assertForbidden();
        $this->actingAs($editor->fresh())->delete(route('news.destroy', $article))->assertForbidden();

        $this->assertDatabaseHas('news_articles', ['id' => $article->id]);
    }

    public function test_a_role_without_admin_access_cannot_reach_the_admin_area(): void
    {
        $role = $this->createRole(['name' => 'community_lead', 'label' => 'Community Lead', 'grants_admin_access' => null]);

        $this->assertFalse($role->grants_admin_access);

        $this->actingAs($this->superAdmin())
            ->patch(route('user-access.permissions.update', $role), [
                'permissions' => Permission::query()->where('name', 'news.view')->pluck('id')->all(),
            ]);

        $lead = $this->member($role, ['name' => 'Cora Lead', 'username' => 'cora-lead']);

        $this->assertFalse($lead->fresh()->isAdmin());
        $this->actingAs($lead)->get(route('dashboard'))->assertRedirect(route('landing'));
        $this->actingAs($lead)->get(route('news.index'))->assertRedirect(route('landing'));
    }

    public function test_role_settings_can_be_edited_and_the_legacy_column_follows(): void
    {
        $role = $this->createRole(['name' => 'community_lead', 'label' => 'Community Lead', 'grants_admin_access' => null]);
        $lead = $this->member($role, ['name' => 'Cora Lead', 'username' => 'cora-lead']);

        $this->assertSame('user', $lead->fresh()->getRawOriginal('role'));

        $this->actingAs($this->superAdmin())
            ->patch(route('user-access.roles.settings.update', $role), [
                'label' => 'Community Lead',
                'name' => 'community_lead',
                'description' => 'County organisers',
                'grants_admin_access' => '1',
                'sort_order' => 45,
            ])
            ->assertRedirect(route('user-access.index').'#roles');

        $this->assertTrue($role->fresh()->grants_admin_access);
        $this->assertSame(45, $role->fresh()->sort_order);
        $this->assertSame('admin', $lead->fresh()->getRawOriginal('role'));
        $this->assertTrue($lead->fresh()->isAdmin());
    }

    public function test_built_in_roles_are_protected(): void
    {
        $superAdmin = $this->superAdmin();

        foreach ([Role::USER, Role::ADMIN, Role::SUPERADMIN] as $name) {
            $this->assertTrue(Role::query()->where('name', $name)->firstOrFail()->isSystem());
        }

        // The built in roles cannot be deleted...
        $this->actingAs($superAdmin)
            ->delete(route('user-access.roles.destroy', Role::query()->where('name', Role::ADMIN)->firstOrFail()))
            ->assertRedirect(route('user-access.index').'#roles')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['name' => Role::ADMIN]);

        // ...their key cannot be changed...
        $adminRole = Role::query()->where('name', Role::ADMIN)->firstOrFail();

        $this->actingAs($superAdmin)
            ->patch(route('user-access.roles.settings.update', $adminRole), [
                'label' => 'Staff',
                'name' => 'staff',
                'grants_admin_access' => '1',
            ])
            ->assertRedirect(route('user-access.index').'#roles');

        $this->assertSame('Staff', $adminRole->fresh()->label);
        $this->assertSame(Role::ADMIN, $adminRole->fresh()->name);

        // ...and the full access role keeps full access.
        $superAdminRole = Role::query()->where('name', Role::SUPERADMIN)->firstOrFail();

        $this->actingAs($superAdmin)
            ->patch(route('user-access.roles.settings.update', $superAdminRole), [
                'label' => 'Super Admin',
                'grants_admin_access' => '1',
                'has_full_access' => null,
            ])
            ->assertSessionHas('error');

        $this->assertTrue($superAdminRole->fresh()->has_full_access);
    }

    public function test_the_full_access_role_keeps_every_permission(): void
    {
        $superAdminRole = Role::query()->where('name', Role::SUPERADMIN)->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->patch(route('user-access.permissions.update', $superAdminRole), ['permissions' => []])
            ->assertSessionHas('error');

        $this->assertTrue($superAdminRole->fresh()->has_full_access);
    }

    public function test_a_role_with_accounts_cannot_be_deleted_or_lose_admin_access(): void
    {
        $role = $this->createRole();
        $this->member($role, ['name' => 'Edna Editor', 'username' => 'edna-editor']);

        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)
            ->delete(route('user-access.roles.destroy', $role))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $role->id]);

        $this->actingAs($superAdmin)
            ->patch(route('user-access.roles.settings.update', $role), [
                'label' => 'Content Editor',
                'grants_admin_access' => null,
            ])
            ->assertSessionHas('error');

        $this->assertTrue($role->fresh()->grants_admin_access);

        // An empty role can be removed.
        $empty = $this->createRole(['name' => 'temporary', 'label' => 'Temporary', 'grants_admin_access' => null]);

        $this->actingAs($superAdmin)
            ->delete(route('user-access.roles.destroy', $empty))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['id' => $empty->id]);
    }

    public function test_create_admin_lists_only_staff_roles_and_uses_the_chosen_one(): void
    {
        $staffRole = $this->createRole();
        $this->createRole(['name' => 'community_lead', 'label' => 'Community Lead', 'grants_admin_access' => null]);

        $page = $this->actingAs($this->superAdmin())->get(route('user-access.index', ['role' => $staffRole->id]))->assertOk();

        $createAdminPanel = $this->panel($page, 'create-admin');

        $this->assertStringContainsString('Content Editor', $createAdminPanel);
        $this->assertStringContainsString('value="'.$staffRole->id.'"', $createAdminPanel);
        // A role without admin access is never offered on the Create Admin form.
        $this->assertStringNotContainsString('Community Lead', $createAdminPanel);

        $this->actingAs($this->superAdmin())
            ->post(route('user-access.admins.store'), [
                'username' => 'edna-editor',
                'name' => 'Edna Editor',
                'email' => 'edna.editor@example.test',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
                'role_id' => $staffRole->id,
            ])
            ->assertRedirect(route('user-access.index'));

        $editor = User::query()->where('username', 'edna-editor')->firstOrFail();

        $this->assertSame($staffRole->id, $editor->role_id);
        $this->assertSame('admin', $editor->getRawOriginal('role'));
        $this->assertTrue($editor->isAdmin());

        // A non staff role is rejected outright.
        $this->actingAs($this->superAdmin())
            ->post(route('user-access.admins.store'), [
                'username' => 'cora-lead',
                'name' => 'Cora Lead',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
                'role_id' => Role::idFor('community_lead'),
            ])
            ->assertSessionHasErrors('role_id');
    }

    public function test_the_last_full_access_account_cannot_be_demoted(): void
    {
        $superAdmin = $this->superAdmin();
        $userRole = Role::query()->where('name', Role::USER)->firstOrFail();

        $this->actingAs($superAdmin)
            ->patch(route('user-access.roles.update', $superAdmin), ['role_id' => $userRole->id])
            ->assertSessionHas('error');

        $this->assertTrue($superAdmin->fresh()->isSuperAdmin());

        // With a second full access account the first may step down.
        $second = User::factory()->create([
            'username' => 'second-root',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);

        $this->actingAs($second)
            ->patch(route('user-access.roles.update', $superAdmin), ['role_id' => $userRole->id])
            ->assertSessionHas('success');

        $this->assertFalse($superAdmin->fresh()->isSuperAdmin());
        $this->assertSame(Role::USER, $superAdmin->fresh()->roleName());
    }

    public function test_only_super_admins_may_manage_roles(): void
    {
        $clerk = $this->member(Role::query()->where('name', Role::ADMIN)->firstOrFail(), [
            'name' => 'Clerk Kim',
            'username' => 'clerk-kim',
        ]);

        $this->actingAs($clerk)->get(route('user-access.index'))->assertOk()->assertDontSee('Create role');

        $this->actingAs($clerk)
            ->post(route('user-access.roles.store'), ['label' => 'Sneaky', 'grants_admin_access' => '1'])
            ->assertForbidden();

        $this->actingAs($clerk)
            ->delete(route('user-access.roles.destroy', Role::query()->where('name', Role::ADMIN)->firstOrFail()))
            ->assertForbidden();

        $this->assertDatabaseMissing('roles', ['label' => 'Sneaky']);
    }
}
