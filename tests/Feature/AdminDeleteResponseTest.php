<?php

namespace Tests\Feature;

use App\Models\Coalition;
use App\Models\PoliticalParty;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Deleting from a list must never replace the page with a JSON payload. The
 * admin layout submits deletes in the background, and the endpoints answer a
 * plain request with a redirect plus a flash so the person stays on the list.
 */
class AdminDeleteResponseTest extends TestCase
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

    private function coalition(): Coalition
    {
        return Coalition::create([
            'name' => 'Test Coalition',
            'content' => 'A coalition.',
            'status' => 'published',
        ]);
    }

    private function party(): PoliticalParty
    {
        return PoliticalParty::create([
            'name' => 'Test Party',
            'abbreviation' => 'TST',
            'content' => 'A party.',
            'status' => 'published',
        ]);
    }

    public function test_deleting_a_coalition_from_the_list_returns_to_the_list_with_a_message(): void
    {
        $coalition = $this->coalition();

        $this->actingAs($this->superAdmin())
            ->from(route('coalitions.index'))
            ->delete(route('coalitions.destroy', $coalition))
            ->assertRedirect(route('coalitions.index'))
            ->assertSessionHas('success', 'Coalition deleted successfully.');

        $this->assertDatabaseMissing('coalitions', ['id' => $coalition->id]);
    }

    public function test_deleting_a_coalition_for_scripted_callers_still_answers_with_json(): void
    {
        $coalition = $this->coalition();

        $this->actingAs($this->superAdmin())
            ->deleteJson(route('coalitions.destroy', $coalition))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Coalition deleted successfully.',
            ]);

        $this->assertDatabaseMissing('coalitions', ['id' => $coalition->id]);
    }

    public function test_deleting_a_political_party_returns_to_the_list_with_a_message(): void
    {
        $party = $this->party();

        $this->actingAs($this->superAdmin())
            ->from(route('political-parties.index'))
            ->delete(route('political-parties.destroy', $party))
            ->assertRedirect(route('political-parties.index'))
            ->assertSessionHas('success', 'Political party deleted successfully.');

        $this->assertDatabaseMissing('political_parties', ['id' => $party->id]);
    }

    public function test_deleting_a_political_party_for_scripted_callers_still_answers_with_json(): void
    {
        $party = $this->party();

        $this->actingAs($this->superAdmin())
            ->deleteJson(route('political-parties.destroy', $party))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Political party deleted successfully.',
            ]);

        $this->assertDatabaseMissing('political_parties', ['id' => $party->id]);
    }

    public function test_the_admin_layout_deletes_in_the_background_instead_of_navigating(): void
    {
        // Guards the raw-JSON page: the confirmation must post in the
        // background and then refresh the list, never submit the browser to
        // the delete URL.
        $html = (string) $this->actingAs($this->superAdmin())
            ->get(route('coalitions.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('function confirmDelete', $html);
        $this->assertStringContainsString("fetch(url, { method: 'POST'", $html);
        $this->assertStringContainsString("credentials: 'same-origin'", $html);
        $this->assertStringContainsString('rememberToast', $html);
        $this->assertStringNotContainsString('form.innerHTML = `@csrf @method', $html);
    }
}
