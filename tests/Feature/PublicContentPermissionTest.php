<?php

namespace Tests\Feature;

use App\Models\Bloc;
use App\Models\County;
use App\Models\Permission;
use App\Models\ResourceLink;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Public Content must be grantable as narrowly as the rest of the system: a
 * clerk who may add links must not automatically be able to delete them, run
 * polls, publish news or regenerate the sitemap.
 */
class PublicContentPermissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every Public Content permission the module is expected to expose.
     *
     * @var list<string>
     */
    private const EXPECTED_PERMISSIONS = [
        'frontend-pages.view', 'frontend-pages.update',
        'news.view', 'news.create', 'news.update', 'news.delete', 'news-comments.moderate',
        'polls.view', 'polls.create', 'polls.update', 'polls.delete', 'poll-comments.moderate',
        'events.view', 'events.create', 'events.update', 'events.delete', 'events.approve',
        'links.view', 'links.create', 'links.update', 'links.delete', 'links.approve',
        'sitemap.view', 'sitemap.regenerate',
        'tags.view', 'tags.create', 'tags.delete',
        'public-pulse.view', 'public-pulse.create', 'public-pulse.update',
    ];

    private function clerk(): User
    {
        return User::factory()->create([
            'name' => 'Clerk Kim',
            'username' => 'clerk-kim',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::ADMIN),
        ]);
    }

    private function county(): County
    {
        $bloc = Bloc::firstOrCreate(['name' => 'Central'], ['tribes' => []]);

        return County::create(['name' => 'Nairobi', 'bloc_id' => $bloc->id]);
    }

    private function grantToAdminRole(array $permissionNames): void
    {
        $ids = Permission::query()->whereIn('name', $permissionNames)->pluck('id');

        Role::query()->where('name', Role::ADMIN)->firstOrFail()->permissions()->syncWithoutDetaching($ids);
    }

    private function revokeFromAdminRole(array $permissionNames): void
    {
        $ids = Permission::query()->whereIn('name', $permissionNames)->pluck('id');

        Role::query()->where('name', Role::ADMIN)->firstOrFail()->permissions()->detach($ids);
    }

    private function adminHoldsEverything(): bool
    {
        $ids = Permission::query()->where('group', 'Public Content')->pluck('id');

        return Role::query()->where('name', Role::ADMIN)->firstOrFail()
            ->permissions()
            ->whereIn('permission_id', $ids)
            ->count() === $ids->count();
    }

    public function test_the_module_exposes_module_specific_permissions(): void
    {
        $names = Permission::query()->where('group', 'Public Content')->pluck('name')->all();

        sort($names);
        $expected = self::EXPECTED_PERMISSIONS;
        sort($expected);

        $this->assertSame($expected, $names);
    }

    public function test_the_blanket_permissions_are_gone(): void
    {
        $this->assertSame(
            0,
            Permission::query()->whereIn('name', ['frontend.view', 'frontend.update'])->count(),
            'The blanket frontend permissions must be replaced by the module specific set.'
        );
    }

    public function test_super_admin_and_clerk_keep_the_whole_module(): void
    {
        $ids = Permission::query()->where('group', 'Public Content')->pluck('id');

        foreach ([Role::SUPERADMIN, Role::ADMIN] as $roleName) {
            $held = Role::query()->where('name', $roleName)->firstOrFail()
                ->permissions()
                ->whereIn('permission_id', $ids)
                ->count();

            $this->assertSame($ids->count(), $held, "The {$roleName} role should hold the whole Public Content set.");
        }

        $this->assertSame(0, Role::query()->where('name', Role::USER)->firstOrFail()->permissions()->count());
    }

    public function test_removing_a_single_permission_only_closes_that_action(): void
    {
        $clerk = $this->clerk();
        $link = ResourceLink::create([
            'user_id' => $clerk->id,
            'platform' => 'facebook_group',
            'title' => 'Bungoma Today',
            'url' => 'https://www.facebook.com/groups/124111804278612',
            'county_id' => $this->county()->id,
        ]);

        $this->revokeFromAdminRole(['links.delete', 'polls.create']);

        // The revoked actions are closed...
        $this->actingAs($clerk)->delete(route('links.destroy', $link))->assertForbidden();
        $this->actingAs($clerk->fresh())->post(route('polls.store'), [])->assertForbidden();

        // ...while everything else in the module still works.
        $this->actingAs($clerk->fresh())->get(route('links.index'))->assertOk();
        $this->actingAs($clerk->fresh())->get(route('links.create'))->assertOk();
        $this->actingAs($clerk->fresh())->get(route('polls.index'))->assertOk();
        $this->actingAs($clerk->fresh())->get(route('news.index'))->assertOk();
        $this->actingAs($clerk->fresh())->get(route('events.index'))->assertOk();
        $this->actingAs($clerk->fresh())->get(route('sitemap.admin'))->assertOk();
    }

    public function test_a_view_only_clerk_cannot_write_news_polls_events_or_links(): void
    {
        $clerk = $this->clerk();

        $this->revokeFromAdminRole([
            'news.create', 'news.update', 'news.delete',
            'polls.create', 'polls.update', 'polls.delete',
            'events.create', 'events.update', 'events.delete', 'events.approve',
            'links.create', 'links.update', 'links.delete', 'links.approve',
            'sitemap.regenerate',
            'tags.create', 'tags.delete',
            'public-pulse.create', 'public-pulse.update',
            'frontend-pages.update',
            'news-comments.moderate', 'poll-comments.moderate',
        ]);

        $this->actingAs($clerk)->get(route('news.index'))->assertOk();
        $this->actingAs($clerk->fresh())->get(route('news.create'))->assertForbidden();
        $this->actingAs($clerk->fresh())->post(route('news.store'), [])->assertForbidden();
        $this->actingAs($clerk->fresh())->get(route('polls.create'))->assertForbidden();
        $this->actingAs($clerk->fresh())->post(route('polls.store'), [])->assertForbidden();
        $this->actingAs($clerk->fresh())->get(route('events.create'))->assertForbidden();
        $this->actingAs($clerk->fresh())->get(route('links.create'))->assertForbidden();
        $this->actingAs($clerk->fresh())->post(route('links.store'), [])->assertForbidden();
        $this->actingAs($clerk->fresh())->post(route('sitemap.regenerate'))->assertForbidden();
        $this->actingAs($clerk->fresh())->get(route('news-comments.index'))->assertForbidden();
        $this->actingAs($clerk->fresh())->get(route('frontend-pages.edit', 1))->assertForbidden();
    }

    public function test_sidebar_items_follow_their_own_permission(): void
    {
        $clerk = $this->clerk();

        $this->actingAs($clerk)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pages & Links')
            ->assertSee('Add Link/Page');

        // Reading links is revoked, creating them is not: only the list entry goes away.
        $this->revokeFromAdminRole(['links.view']);

        $this->actingAs($clerk->fresh())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Pages & Links')
            ->assertSee('Add Link/Page');
    }

    public function test_the_split_migration_round_trips(): void
    {
        $migration = require database_path(
            'migrations/2026_10_12_000002_split_public_content_permissions.php'
        );

        $migration->down();

        $this->assertSame(
            2,
            Permission::query()->whereIn('name', ['frontend.view', 'frontend.update'])->count(),
            'Rolling back must restore the two legacy permissions.'
        );

        $this->assertSame(2, Permission::query()->where('group', 'Public Content')->count());

        $adminRole = Role::query()->where('name', Role::ADMIN)->firstOrFail();
        $this->assertSame(
            2,
            $adminRole->permissions()->where('group', 'Public Content')->count(),
            'The pre-existing grants must survive the rollback.'
        );

        $migration->up();

        $this->assertSame(0, Permission::query()->whereIn('name', ['frontend.view', 'frontend.update'])->count());
        $this->assertSame(count(self::EXPECTED_PERMISSIONS), Permission::query()->where('group', 'Public Content')->count());
        $this->assertTrue($this->adminHoldsEverything());
    }

    public function test_the_role_permissions_screen_lists_the_new_permissions(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);

        $this->actingAs($superAdmin)
            ->get(route('user-access.index'))
            ->assertOk()
            ->assertSee('Public Content')
            ->assertSee('Add News')
            ->assertSee('Delete News')
            ->assertSee('Add Polls')
            ->assertSee('Delete Polls')
            ->assertSee('Add Pages &amp; Links', false)
            ->assertSee('Approve Pages &amp; Links', false)
            ->assertSee('Regenerate Sitemap')
            ->assertSee('Moderate News Comments')
            ->assertSee('Moderate Poll Comments');
    }
}
