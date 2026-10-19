<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\CoordinatorsGuide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Coordinators Guide is the internal handbook: every staff member may read
 * it, and only roles holding guide.update may rewrite it.
 */
class CoordinatorsGuideTest extends TestCase
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
     * @param  list<string>  $permissionNames
     */
    private function staffWith(array $permissionNames, string $roleName = 'coordinator'): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            [
                'label' => 'Coordinator',
                'description' => 'Runs the day to day coordination work',
                'grants_admin_access' => true,
                'has_full_access' => false,
                'is_system' => false,
                'sort_order' => 45,
            ]
        );

        $role->permissions()->sync(
            Permission::query()->whereIn('name', $permissionNames)->pluck('id')->all()
        );

        return User::factory()->create([
            'name' => 'Staff '.$roleName,
            'username' => 'staff-'.$roleName,
            'role' => 'admin',
            'role_id' => $role->id,
        ]);
    }

    public function test_the_guide_ships_with_the_team_permissions(): void
    {
        $this->assertDatabaseHas('permissions', ['name' => 'guide.view', 'group' => 'Team Guide']);
        $this->assertDatabaseHas('permissions', ['name' => 'guide.update', 'group' => 'Team Guide']);

        // Existing staff roles keep their reach: reading is granted out of the box.
        $this->assertTrue(Role::query()->where('name', Role::ADMIN)->firstOrFail()->hasPermission('guide.view'));
        $this->assertTrue(Role::query()->where('name', Role::ADMIN)->firstOrFail()->hasPermission('guide.update'));
    }

    public function test_staff_can_read_the_built_in_guide(): void
    {
        $response = $this->actingAs($this->staffWith(['guide.view']))
            ->get(route('coordinators-guide.index'))
            ->assertOk()
            ->assertSee('Coordinators Guide')
            ->assertSee('You are reading the built-in guide');

        // The document is rendered as headings with anchors and a contents list.
        $response->assertSee('<h2 id="what-this-guide-is-for">', false);
        $response->assertSee('href="#what-this-guide-is-for"', false);

        // A reader does not get the editor.
        $response->assertDontSee('Save guide');
    }

    public function test_staff_without_the_read_permission_are_forbidden(): void
    {
        $this->actingAs($this->staffWith(['news.view'], 'news-reader'))
            ->get(route('coordinators-guide.index'))
            ->assertForbidden();
    }

    public function test_an_editor_can_rewrite_the_guide(): void
    {
        $editor = $this->staffWith(['guide.view', 'guide.update'], 'guide-editor');

        $this->actingAs($editor)
            ->put(route('coordinators-guide.update'), [
                'body' => "## Opening hours\n\nBe at your desk by 8am.\n\n- Check the queue\n- Read Popular Content",
            ])
            ->assertRedirect(route('coordinators-guide.index'))
            ->assertSessionHas('success');

        $this->assertSame(
            "## Opening hours\n\nBe at your desk by 8am.\n\n- Check the queue\n- Read Popular Content",
            Setting::query()->where('key', CoordinatorsGuide::SETTING_KEY)->value('value')
        );

        $this->actingAs($editor)
            ->get(route('coordinators-guide.index'))
            ->assertOk()
            ->assertSee('<h2 id="opening-hours">', false)
            ->assertSee('Be at your desk by 8am.')
            ->assertSee('Save guide')
            ->assertDontSee('You are reading the built-in guide');
    }

    public function test_a_reader_cannot_rewrite_the_guide(): void
    {
        $this->actingAs($this->staffWith(['guide.view'], 'guide-reader'))
            ->put(route('coordinators-guide.update'), ['body' => '## Anything'])
            ->assertForbidden();

        $this->assertDatabaseMissing('settings', ['key' => CoordinatorsGuide::SETTING_KEY]);
    }

    public function test_the_editor_cannot_save_an_empty_guide(): void
    {
        $editor = $this->staffWith(['guide.view', 'guide.update'], 'guide-editor-two');

        $this->actingAs($editor)
            ->put(route('coordinators-guide.update'), ['body' => ''])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseMissing('settings', ['key' => CoordinatorsGuide::SETTING_KEY]);
    }

    public function test_markup_written_into_the_guide_is_escaped(): void
    {
        $editor = $this->staffWith(['guide.view', 'guide.update'], 'guide-editor-three');

        $this->actingAs($editor)
            ->put(route('coordinators-guide.update'), [
                'body' => "## Careful\n\n<script>alert('xss')</script>",
            ])
            ->assertRedirect(route('coordinators-guide.index'));

        $page = $this->actingAs($editor)->get(route('coordinators-guide.index'))->assertOk();

        $page->assertDontSee("<script>alert('xss')</script>", false);
        $page->assertSee('&lt;script&gt;', false);
    }

    public function test_the_sidebar_offers_the_guide_to_staff_who_may_read_it(): void
    {
        $this->actingAs($this->staffWith(['dashboard.view', 'guide.view'], 'sidebar-reader'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Coordinators Guide')
            ->assertSee(route('coordinators-guide.index'), false);

        $this->actingAs($this->staffWith(['dashboard.view'], 'sidebar-outsider'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('coordinators-guide.index'), false);
    }

    public function test_the_super_admin_can_read_and_edit_the_guide(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('coordinators-guide.index'))
            ->assertOk()
            ->assertSee('Save guide');

        $this->actingAs($this->superAdmin())
            ->put(route('coordinators-guide.update'), ['body' => '## Our week'])
            ->assertRedirect(route('coordinators-guide.index'));
    }

    public function test_the_built_in_guide_describes_the_modules_people_run(): void
    {
        $guide = CoordinatorsGuide::defaultDocument();

        foreach ([
            'Roles and permissions',
            'Voter management',
            'Communications',
            'Aspirant campaigns',
            'Public content',
            'Token management and finance',
            'Looking after this guide',
        ] as $section) {
            $this->assertStringContainsString($section, $guide);
        }
    }
}
