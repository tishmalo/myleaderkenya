<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UserAccessRoleAllocationFilterTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);
    }

    private function member(string $roleName, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $roleName === Role::USER ? 'user' : 'admin',
            'role_id' => Role::idFor($roleName),
        ], $attributes));
    }

    /**
     * The Role Allocation panel markup only, so assertions are not confused by
     * the Admin Accounts panel, which legitimately lists admin users too.
     */
    private function allocationMarkup(TestResponse $response): string
    {
        $html = (string) $response->getContent();
        $start = strpos($html, 'data-tab-panel="allocation"');

        $this->assertNotFalse($start, 'The Role Allocation panel is missing from the page.');

        $panel = substr($html, (int) $start);
        $end = strpos($panel, '</section>');

        return $end === false ? $panel : substr($panel, 0, $end);
    }

    /**
     * @param  array<string, string>  $query
     * @param  list<string>  $expected
     * @param  list<string>  $unexpected
     */
    private function assertAllocationListing(User $admin, array $query, array $expected, array $unexpected = []): TestResponse
    {
        $response = $this->actingAs($admin)
            ->get(route('user-access.index', $query))
            ->assertOk();

        $allocation = $this->allocationMarkup($response);

        foreach ($expected as $value) {
            $this->assertStringContainsString($value, $allocation);
        }

        foreach ($unexpected as $value) {
            $this->assertStringNotContainsString($value, $allocation);
        }

        return $response;
    }

    public function test_role_allocation_can_be_searched_by_name_username_and_email(): void
    {
        $admin = $this->superAdmin();

        $this->member(Role::USER, [
            'name' => 'Phelix Onyango Okello',
            'username' => 'phelixokello945',
            'email' => 'phelixokello945@gmail.com',
        ]);

        $this->member(Role::USER, [
            'name' => 'Joseph Mutua Musau',
            'username' => 'josephmutausau1996',
            'email' => 'josephmutausau1996@gmail.com',
        ]);

        $this->assertAllocationListing(
            $admin,
            ['search' => 'Phelix Onyango'],
            ['Phelix Onyango Okello'],
            ['Joseph Mutua Musau'],
        );

        $this->assertAllocationListing(
            $admin,
            ['search' => 'josephmutausau1996'],
            ['Joseph Mutua Musau'],
            ['Phelix Onyango Okello'],
        );

        $this->assertAllocationListing(
            $admin,
            ['search' => 'phelixokello945@gmail.com'],
            ['Phelix Onyango Okello'],
            ['Joseph Mutua Musau'],
        );

        $this->assertAllocationListing(
            $admin,
            ['search' => 'PHELIXOKELLO945@GMAIL.COM'],
            ['Phelix Onyango Okello'],
            ['Joseph Mutua Musau'],
        );
    }

    public function test_role_allocation_can_be_filtered_by_role(): void
    {
        $admin = $this->superAdmin();

        $this->member(Role::USER, ['name' => 'Voter Vera', 'username' => 'voter-vera']);
        $this->member(Role::ADMIN, ['name' => 'Admin Ada', 'username' => 'admin-ada']);
        $this->member(Role::SUPERADMIN, ['name' => 'Chief Chloe', 'username' => 'chief-chloe']);

        $this->assertAllocationListing(
            $admin,
            ['role' => Role::ADMIN],
            ['Admin Ada'],
            ['Voter Vera', 'Chief Chloe'],
        );

        $this->assertAllocationListing(
            $admin,
            ['role' => Role::SUPERADMIN],
            ['Chief Chloe'],
            ['Voter Vera', 'Admin Ada'],
        );

        $this->assertAllocationListing(
            $admin,
            ['role' => Role::USER],
            ['Voter Vera'],
            ['Admin Ada', 'Chief Chloe'],
        );
    }

    public function test_search_and_role_filter_apply_together(): void
    {
        $admin = $this->superAdmin();

        $this->member(Role::USER, ['name' => 'Grace Wanjiru', 'username' => 'grace-wanjiru']);
        $this->member(Role::ADMIN, ['name' => 'Grace Admin', 'username' => 'grace-admin']);

        $this->assertAllocationListing(
            $admin,
            ['search' => 'Grace', 'role' => Role::USER],
            ['Grace Wanjiru'],
            ['Grace Admin'],
        );

        $this->assertAllocationListing(
            $admin,
            ['search' => 'Grace', 'role' => Role::ADMIN],
            ['Grace Admin'],
            ['Grace Wanjiru'],
        );
    }

    public function test_unknown_role_filter_is_ignored(): void
    {
        $admin = $this->superAdmin();

        $this->member(Role::USER, ['name' => 'Voter Vera', 'username' => 'voter-vera']);

        $this->assertAllocationListing($admin, ['role' => 'not-a-real-role'], ['Voter Vera']);
    }

    public function test_filters_are_kept_on_pagination_and_in_the_filter_controls(): void
    {
        $admin = $this->superAdmin();

        foreach (range(1, 21) as $index) {
            $this->member(Role::USER, [
                'name' => "Probe User {$index}",
                'username' => "probe-user-{$index}",
                'email' => "probe-user-{$index}@example.com",
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('user-access.index', ['search' => 'Probe', 'role' => Role::USER]))
            ->assertOk()
            ->assertSee('21 users found')
            ->assertSee('data-allocation-filter', false)
            ->assertSee('value="Probe"', false)
            ->assertSee('Filter role');

        // Pagination links must carry the active filters forward.
        $response->assertSee('search=Probe', false);
        $response->assertSee('role=user', false);
    }

    public function test_role_save_returns_to_the_role_allocation_tab(): void
    {
        $admin = $this->superAdmin();
        $target = $this->member(Role::USER, ['name' => 'Voter Vera', 'username' => 'voter-vera']);

        $this->actingAs($admin)
            ->from(route('user-access.index').'#allocation')
            ->patch(route('user-access.roles.update', $target), ['role_id' => Role::idFor(Role::ADMIN)])
            ->assertRedirect(route('user-access.index').'#allocation');

        $this->assertSame(Role::ADMIN, $target->fresh()->roleName());
    }
}
