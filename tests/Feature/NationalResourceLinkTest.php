<?php

namespace Tests\Feature;

use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\County;
use App\Models\Position;
use App\Models\ResourceLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Presidential aspirants have no county, so a link for one has to be allowed
 * without a county and must publish on the national aspirants page
 * (aspirants?position=<president>) and on that aspirant's profile - never on a
 * county page.
 */
class NationalResourceLinkTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'superadmin',
        ]);
    }

    private function county(string $name = 'Nairobi'): County
    {
        $bloc = Bloc::firstOrCreate(['name' => 'Central'], ['tribes' => []]);

        return County::create(['name' => $name, 'bloc_id' => $bloc->id]);
    }

    private function position(string $name): Position
    {
        return Position::firstOrCreate(['name' => $name], ['description' => $name]);
    }

    private function presidentialCandidate(string $name = 'Edwin Sifuna'): Candidate
    {
        return Candidate::create([
            'name' => $name,
            'position_id' => $this->position('President')->id,
            'country' => 'Kenya',
            'approval_status' => 'approved',
        ]);
    }

    private function countyCandidate(string $name = 'County MP', string $county = 'Nairobi'): Candidate
    {
        return Candidate::create([
            'name' => $name,
            'position_id' => $this->position('Member of Parliament')->id,
            'country' => 'Kenya',
            'county' => $county,
            'approval_status' => 'approved',
        ]);
    }

    private function nationalPayload(array $overrides = []): array
    {
        return array_merge([
            'platform' => 'facebook_page',
            'title' => 'Edwin W Sifuna FB Page',
            'url' => 'https://www.facebook.com/EdwinWSifuna/',
            'is_national' => '1',
            'followers' => '5400',
        ], $overrides);
    }

    private function countyPayload(County $county, array $overrides = []): array
    {
        return array_merge([
            'platform' => 'facebook_group',
            'title' => 'Nairobi County Announcements',
            'url' => 'https://www.facebook.com/groups/nairobi/',
            'county_id' => $county->id,
        ], $overrides);
    }

    public function test_admin_can_add_a_national_link_for_a_presidential_aspirant(): void
    {
        $candidate = $this->presidentialCandidate();
        $county = $this->county();

        $this->actingAs($this->admin())
            ->post(route('links.store'), $this->nationalPayload([
                'candidate_id' => $candidate->id,
                // A stale county from the form must not survive.
                'county_id' => $county->id,
            ]))
            ->assertRedirect(route('links.index'))
            ->assertSessionHas('success');

        $link = ResourceLink::query()->firstOrFail();

        $this->assertNull($link->county_id);
        $this->assertSame($candidate->id, $link->candidate_id);
        $this->assertTrue($link->isNational());
    }

    public function test_a_presidential_aspirant_makes_the_link_national_without_the_toggle(): void
    {
        $candidate = $this->presidentialCandidate();

        $this->actingAs($this->admin())
            ->post(route('links.store'), $this->nationalPayload([
                'candidate_id' => $candidate->id,
                'is_national' => null,
            ]))
            ->assertRedirect(route('links.index'))
            ->assertSessionHas('success');

        $this->assertNull(ResourceLink::query()->firstOrFail()->county_id);
    }

    public function test_a_county_link_still_needs_a_county(): void
    {
        $countyCandidate = $this->countyCandidate();

        $this->actingAs($this->admin())
            ->post(route('links.store'), [
                'platform' => 'facebook_page',
                'title' => 'No scope at all',
                'url' => 'https://www.facebook.com/noscope/',
                'candidate_id' => $countyCandidate->id,
            ])
            ->assertSessionHasErrors('county_id');

        $this->assertDatabaseCount('resource_links', 0);
    }

    public function test_national_links_publish_on_the_national_aspirants_page(): void
    {
        $admin = $this->admin();
        $candidate = $this->presidentialCandidate();
        $county = $this->county();

        $this->actingAs($admin)->post(route('links.store'), $this->nationalPayload([
            'candidate_id' => $candidate->id,
        ]));

        $this->actingAs($admin)->post(route('links.store'), $this->countyPayload($county, [
            'title' => 'Nairobi County Announcements',
        ]));

        $national = ResourceLink::query()->whereNull('county_id')->firstOrFail();
        $countyLink = ResourceLink::query()->whereNotNull('county_id')->firstOrFail();

        // Links only publish once approved.
        $this->get(route('aspirants.public', ['position' => $candidate->position_id]))
            ->assertOk()
            ->assertDontSee('Edwin W Sifuna FB Page');

        ResourceLink::query()->update(['approval_status' => ResourceLink::STATUS_APPROVED]);

        $this->get(route('aspirants.public', ['position' => $candidate->position_id]))
            ->assertOk()
            ->assertSee('National pages & links')
            ->assertSee('Edwin W Sifuna FB Page')
            ->assertDontSee('Nairobi County Announcements');

        $this->assertTrue($national->fresh()->isNational());
        $this->assertFalse($countyLink->fresh()->isNational());
    }

    public function test_a_national_link_appears_on_its_aspirants_profile(): void
    {
        $admin = $this->admin();
        $candidate = $this->presidentialCandidate();

        $this->actingAs($admin)->post(route('links.store'), $this->nationalPayload([
            'candidate_id' => $candidate->id,
        ]));

        ResourceLink::query()->update(['approval_status' => ResourceLink::STATUS_APPROVED]);

        $this->get(route('aspirants.show', $candidate))
            ->assertOk()
            ->assertSee('Edwin W Sifuna FB Page');
    }

    public function test_a_national_link_stays_off_county_pages(): void
    {
        $admin = $this->admin();
        $county = $this->county();

        $this->actingAs($admin)->post(route('links.store'), $this->nationalPayload());

        ResourceLink::query()->update(['approval_status' => ResourceLink::STATUS_APPROVED]);

        $this->get(route('county.show', $county))
            ->assertOk()
            ->assertDontSee('Edwin W Sifuna FB Page');
    }

    public function test_admin_can_turn_a_county_link_into_a_national_one(): void
    {
        $admin = $this->admin();
        $county = $this->county();

        $this->actingAs($admin)->post(route('links.store'), $this->countyPayload($county));

        $link = ResourceLink::query()->firstOrFail();
        $this->assertSame($county->id, $link->county_id);

        $this->actingAs($admin)
            ->put(route('links.update', $link), $this->nationalPayload([
                'title' => 'Kenya wide group',
                'county_id' => $county->id,
            ]))
            ->assertRedirect(route('links.index'))
            ->assertSessionHas('success');

        $this->assertNull($link->fresh()->county_id);

        // ...and back again.
        $this->actingAs($admin)
            ->put(route('links.update', $link), $this->countyPayload($county, [
                'title' => 'Back to Nairobi',
            ]))
            ->assertRedirect(route('links.index'));

        $this->assertSame($county->id, $link->fresh()->county_id);
    }

    public function test_editing_a_county_link_cannot_drop_its_county(): void
    {
        $admin = $this->admin();
        $county = $this->county();

        $this->actingAs($admin)->post(route('links.store'), $this->countyPayload($county));

        $link = ResourceLink::query()->firstOrFail();

        $this->actingAs($admin)
            ->put(route('links.update', $link), [
                'platform' => 'facebook_group',
                'title' => 'Nairobi County Announcements',
                'url' => 'https://www.facebook.com/groups/nairobi/',
                'county_id' => '',
            ])
            ->assertSessionHasErrors('county_id');

        $this->assertSame($county->id, $link->fresh()->county_id);
    }

    public function test_the_admin_list_labels_national_links(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('links.store'), $this->nationalPayload());

        $this->actingAs($admin)
            ->get(route('links.index'))
            ->assertOk()
            ->assertSee('National')
            ->assertSee('Edwin W Sifuna FB Page');
    }

    public function test_the_add_form_explains_the_national_option(): void
    {
        $this->actingAs($this->admin())
            ->get(route('links.create'))
            ->assertOk()
            ->assertSee('National link (no county)')
            ->assertSee('is_national', false);
    }
}
