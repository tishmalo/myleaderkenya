<?php

namespace Tests\Feature;

use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\County;
use App\Models\Position;
use App\Models\ResourceLink;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Community pages and links are for Niko Kadi verified members: visitors may see
 * that a link exists, but only registered voters (and staff) get a working link.
 */
class MemberLinkGateTest extends TestCase
{
    use RefreshDatabase;

    private function county(): County
    {
        $bloc = Bloc::firstOrCreate(['name' => 'Western'], ['tribes' => []]);

        return County::create(['name' => 'Bungoma', 'bloc_id' => $bloc->id]);
    }

    private function link(County $county): ResourceLink
    {
        return ResourceLink::create([
            'platform' => 'facebook_group',
            'title' => 'BUNGOMA TODAY',
            'url' => 'https://www.facebook.com/groups/124111804278612',
            'county_id' => $county->id,
            'followers' => 54500,
            'approval_status' => ResourceLink::STATUS_APPROVED,
        ]);
    }

    private function member(string $username, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Member '.$username,
            'username' => $username,
            'role' => 'user',
            'role_id' => Role::idFor(Role::USER),
        ], $attributes));
    }

    public function test_a_visitor_sees_the_link_but_not_its_destination(): void
    {
        $county = $this->county();
        $this->link($county);

        $response = $this->get(route('county.show', $county))->assertOk();

        // The card is visible so the visitor knows the page exists...
        $response->assertSee('BUNGOMA TODAY');
        $response->assertSee('Registered voters only');

        // ...but the destination is never rendered for them.
        $response->assertDontSee('https://www.facebook.com/groups/124111804278612', false);
        $response->assertSee('Login to view');
    }

    public function test_the_popup_asks_for_a_login_or_registration(): void
    {
        $county = $this->county();
        $this->link($county);

        $this->get(route('county.show', $county))
            ->assertOk()
            ->assertSee('Only registered voters can view this page. Login or register to view this link')
            ->assertSee(route('login'), false)
            ->assertSee(route('register'), false);
    }

    public function test_an_unverified_member_is_gated(): void
    {
        $county = $this->county();
        $this->link($county);

        $unverified = $this->member('unverified', [
            'is_voter' => false,
            'is_registered' => false,
        ]);

        $this->actingAs($unverified)
            ->get(route('county.show', $county))
            ->assertOk()
            ->assertSee('BUNGOMA TODAY')
            ->assertDontSee('https://www.facebook.com/groups/124111804278612', false);
    }

    public function test_a_registered_voter_gets_a_working_link(): void
    {
        $county = $this->county();
        $this->link($county);

        $voter = $this->member('voter', ['is_voter' => true]);

        $this->actingAs($voter)
            ->get(route('county.show', $county))
            ->assertOk()
            ->assertSee('BUNGOMA TODAY')
            ->assertSee('https://www.facebook.com/groups/124111804278612', false)
            ->assertDontSee('Only registered voters can view this page');
    }

    public function test_the_registered_flag_also_unlocks_the_links(): void
    {
        $county = $this->county();
        $this->link($county);

        $registered = $this->member('registered', ['is_registered' => true]);

        $this->actingAs($registered)
            ->get(route('county.show', $county))
            ->assertOk()
            ->assertSee('https://www.facebook.com/groups/124111804278612', false);
    }

    public function test_staff_can_always_open_the_links(): void
    {
        $county = $this->county();
        $this->link($county);

        $admin = User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'superadmin',
        ]);

        $this->actingAs($admin)
            ->get(route('county.show', $county))
            ->assertOk()
            ->assertSee('https://www.facebook.com/groups/124111804278612', false);
    }

    public function test_the_same_gate_applies_on_an_aspirant_profile(): void
    {
        $county = $this->county();
        $candidate = Candidate::create([
            'name' => 'Vincent Simiyu Wanjal',
            'position_id' => Position::firstOrCreate(['name' => 'Governor'], ['description' => 'Governor'])->id,
            'country' => 'Kenya',
            'county' => 'Bungoma',
            'approval_status' => 'approved',
        ]);

        ResourceLink::create([
            'platform' => 'facebook_page',
            'title' => 'Simiyu Campaign Page',
            'url' => 'https://www.facebook.com/SimiyuCampaign',
            'county_id' => $county->id,
            'candidate_id' => $candidate->id,
            'approval_status' => ResourceLink::STATUS_APPROVED,
        ]);

        $this->get(route('aspirants.show', $candidate))
            ->assertOk()
            ->assertSee('Simiyu Campaign Page')
            ->assertDontSee('https://www.facebook.com/SimiyuCampaign', false);

        $voter = $this->member('voter', ['is_voter' => true]);

        $this->actingAs($voter)
            ->get(route('aspirants.show', $candidate))
            ->assertOk()
            ->assertSee('https://www.facebook.com/SimiyuCampaign', false);
    }
}
