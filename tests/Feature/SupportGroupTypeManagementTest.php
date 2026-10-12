<?php

namespace Tests\Feature;

use App\Models\SupportGroupType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportGroupTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    public function test_admin_can_view_the_support_group_types(): void
    {
        $this->actingAs($this->admin())
            ->get(route('support-group-types.index'))
            ->assertOk()
            ->assertSee('Friends')
            ->assertSee('Family');
    }

    public function test_admin_can_create_a_support_group_type(): void
    {
        $this->actingAs($this->admin())
            ->from(route('support-group-types.index'))
            ->post(route('support-group-types.store'), [
                'name' => 'Volunteer Network',
                'sort_order' => 5,
                'is_active' => '1',
            ])
            ->assertRedirect(route('support-group-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('support_group_types', [
            'name' => 'Volunteer Network',
            'slug' => 'volunteer-network',
            'sort_order' => 5,
            'is_active' => 1,
        ]);
    }

    public function test_a_group_type_defaults_to_active_when_not_supplied(): void
    {
        $this->actingAs($this->admin())
            ->from(route('support-group-types.index'))
            ->post(route('support-group-types.store'), ['name' => 'Friends of the Campaign'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('support_group_types', [
            'slug' => 'friends-of-the-campaign',
            'is_active' => 1,
        ]);
    }

    public function test_the_name_must_be_unique(): void
    {
        $this->actingAs($this->admin())
            ->from(route('support-group-types.index'))
            ->post(route('support-group-types.store'), ['name' => 'Friends'])
            ->assertSessionHasErrors('name');
    }

    public function test_a_new_group_type_requires_a_name(): void
    {
        $this->actingAs($this->admin())
            ->from(route('support-group-types.index'))
            ->post(route('support-group-types.store'), [])
            ->assertSessionHasErrors('name');
    }

    public function test_updating_to_another_groups_name_is_rejected(): void
    {
        SupportGroupType::create(['name' => 'Volunteers', 'slug' => 'volunteers', 'is_active' => true]);
        $family = SupportGroupType::create(['name' => 'Youth Wing', 'slug' => 'youth-wing', 'is_active' => true]);

        $this->actingAs($this->admin())
            ->from(route('support-group-types.index'))
            ->patch(route('support-group-types.update', $family), ['name' => 'Volunteers'])
            ->assertSessionHasErrors('name');

        $this->assertSame('Youth Wing', $family->fresh()->name);
    }

    public function test_admin_can_update_a_support_group_type(): void
    {
        $type = SupportGroupType::create([
            'name' => 'Volunteers',
            'slug' => 'volunteers',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->from(route('support-group-types.index'))
            ->patch(route('support-group-types.update', $type), [
                'name' => 'Volunteer Network',
                'sort_order' => 9,
            ])
            ->assertRedirect(route('support-group-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('support_group_types', [
            'id' => $type->id,
            'name' => 'Volunteer Network',
            'slug' => 'volunteer-network',
            'sort_order' => 9,
            'is_active' => 0,
        ]);
    }

    public function test_admin_can_delete_an_empty_support_group_type(): void
    {
        $type = SupportGroupType::create([
            'name' => 'Volunteers',
            'slug' => 'volunteers',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->from(route('support-group-types.index'))
            ->delete(route('support-group-types.destroy', $type))
            ->assertRedirect(route('support-group-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('support_group_types', ['id' => $type->id]);
    }
}
