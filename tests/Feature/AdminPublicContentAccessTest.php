<?php

namespace Tests\Feature;

use App\Models\Bloc;
use App\Models\County;
use App\Models\Permission;
use App\Models\Poll;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Clerks (the admin role) work the Public Content queue: adding links/pages,
 * creating polls and publishing news. That module is gated by module specific
 * permissions under the Public Content group, which this suite pins down.
 */
class AdminPublicContentAccessTest extends TestCase
{
    use RefreshDatabase;

    private static ?object $publicContentGrantMigration = null;

    private function clerk(): User
    {
        return User::factory()->create([
            'name' => 'Clerk Kim',
            'username' => 'clerk-kim',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::ADMIN),
        ]);
    }

    /**
     * A completed profile keeps the `profile.complete` gate out of the way so
     * the assertion really covers the admin/permission gate.
     */
    private function regularUser(): User
    {
        return User::factory()->create([
            'name' => 'Voter Vic',
            'username' => 'voter-vic',
            'role' => 'user',
            'role_id' => Role::idFor(Role::USER),
            'gender' => 'female',
            'year_of_birth' => 1990,
            'county' => 'Nyandarua',
            'constituency' => 'Mathi East',
            'ward' => 'Kaguru',
            'country_of_residence' => 'Kenya',
        ]);
    }

    private function county(): County
    {
        $bloc = Bloc::firstOrCreate(['name' => 'Central'], ['tribes' => []]);

        return County::create(['name' => 'Nairobi', 'bloc_id' => $bloc->id]);
    }

    /**
     * Runs the splitting migration again, the way a deploy would when the admin
     * role lost the permissions after the fact.
     */
    private function runPublicContentMigration(): void
    {
        $migration = self::$publicContentGrantMigration ??= require database_path(
            'migrations/2026_10_12_000002_split_public_content_permissions.php'
        );

        $migration->up();
    }

    public function test_admin_role_holds_the_public_content_permissions(): void
    {
        $this->assertTrue($this->adminHasPublicContentPermissions());

        $this->assertSame(
            0,
            Role::query()->where('name', Role::USER)->firstOrFail()
                ->permissions()
                ->whereIn('name', Permission::query()->where('group', 'Public Content')->pluck('name'))
                ->count(),
            'Regular users must not gain public content access.'
        );
    }

    public function test_clerk_sees_the_public_content_links_in_the_sidebar(): void
    {
        $this->actingAs($this->clerk())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Public Content')
            ->assertSee('News')
            ->assertSee('Polls')
            ->assertSee('Pages & Links')
            ->assertSee('Add Link/Page')
            ->assertSee(route('links.create'), false)
            ->assertSee(route('polls.index'), false);
    }

    public function test_clerk_can_open_the_public_content_admin_pages(): void
    {
        $clerk = $this->clerk();

        foreach ([
            route('links.index'),
            route('links.create'),
            route('polls.index'),
            route('polls.create'),
        ] as $url) {
            $this->actingAs($clerk)->get($url)->assertOk();
        }
    }

    public function test_clerk_can_add_a_link_and_create_a_poll(): void
    {
        $clerk = $this->clerk();
        $county = $this->county();

        $this->actingAs($clerk)
            ->post(route('links.store'), [
                'platform' => 'facebook_group',
                'title' => 'Bungoma Today',
                'url' => 'https://www.facebook.com/groups/124111804278612',
                'county_id' => $county->id,
                'followers' => 5400,
            ])
            ->assertRedirect(route('links.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('resource_links', [
            'title' => 'Bungoma Today',
            'platform' => 'facebook_group',
            'user_id' => $clerk->id,
        ]);

        $this->actingAs($clerk)
            ->post(route('polls.store'), [
                'question' => 'Who should lead Bungoma?',
                'poll_type' => Poll::TYPE_WORDS,
                'status' => Poll::STATUS_DRAFT,
                'ends_at' => now()->addWeek()->format('Y-m-d H:i:s'),
                'options' => [
                    ['label' => 'Option one'],
                    ['label' => 'Option two'],
                ],
            ])
            ->assertRedirect(route('polls.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('polls', [
            'question' => 'Who should lead Bungoma?',
            'created_by' => $clerk->id,
        ]);
    }

    public function test_migration_restores_access_when_the_permissions_were_removed(): void
    {
        $this->removePublicContentPermissionsFromAdminRole();

        $clerk = $this->clerk();

        $this->actingAs($clerk)->get(route('links.index'))->assertForbidden();

        $this->runPublicContentMigration();

        $this->assertTrue($this->adminHasPublicContentPermissions());

        $this->actingAs($clerk->fresh())->get(route('links.index'))->assertOk();
    }

    public function test_regular_users_still_cannot_reach_the_public_content_admin(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)->get(route('links.index'))->assertRedirect(route('landing'));
        $this->actingAs($user)->get(route('polls.create'))->assertRedirect(route('landing'));
        $this->actingAs($user)->post(route('links.store'), [])->assertRedirect(route('landing'));
    }

    private function adminHasPublicContentPermissions(): bool
    {
        $groupPermissions = Permission::query()->where('group', 'Public Content')->pluck('id');

        return Role::query()->where('name', Role::ADMIN)->firstOrFail()
            ->permissions()
            ->whereIn('permission_id', $groupPermissions)
            ->count() === $groupPermissions->count();
    }

    private function removePublicContentPermissionsFromAdminRole(): void
    {
        $permissionIds = Permission::query()->where('group', 'Public Content')->pluck('id');

        Role::query()->where('name', Role::ADMIN)->firstOrFail()->permissions()->detach($permissionIds);
    }
}
