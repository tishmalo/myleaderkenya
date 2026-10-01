<?php

namespace Tests\Feature;

use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\Constituency;
use App\Models\County;
use App\Models\PoliticalParty;
use App\Models\Position;
use App\Models\ResourceLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceLinkFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    /**
     * The account routes sit behind the `profile.complete` gate, so ordinary
     * submitters need every required profile field filled in.
     */
    private function submitter(string $email = 'jane.citizen@example.com'): User
    {
        return User::factory()->create([
            'name' => 'Jane Citizen',
            'email' => $email,
            'gender' => 'female',
            'year_of_birth' => 1990,
            'county' => 'Nyandarua',
            'constituency' => 'Mathi East',
            'ward' => 'Kaguru',
            'country_of_residence' => 'Kenya',
        ]);
    }

    private function county(string $name = 'Nyandarua'): County
    {
        $bloc = Bloc::firstOrCreate(['name' => 'Central'], ['tribes' => []]);

        return County::create(['name' => $name, 'bloc_id' => $bloc->id]);
    }

    private function constituency(County $county, string $name = 'Mathi East'): Constituency
    {
        return Constituency::create(['name' => $name, 'county_id' => $county->id]);
    }

    private function candidate(string $name, string $county, array $overrides = []): Candidate
    {
        $position = Position::firstOrCreate(['name' => 'Member of Parliament'], ['description' => 'MP']);

        return Candidate::create(array_merge([
            'name' => $name,
            'position_id' => $position->id,
            'country' => 'Kenya',
            'county' => $county,
            'approval_status' => 'approved',
        ], $overrides));
    }

    private function payload(County $county, array $overrides = []): array
    {
        return array_merge([
            'platform' => 'facebook_group',
            'url' => 'https://www.facebook.com/share/g/1UqwN8Sw26/',
            'county_id' => $county->id,
            'followers' => '12500',
            'comment' => 'A county wide WhatsApp group.',
        ], $overrides);
    }

    public function test_guests_are_redirected_from_the_link_pages(): void
    {
        $county = $this->county();

        $this->get(route('account.links.index'))->assertRedirect(route('login'));
        $this->get(route('account.links.create'))->assertRedirect(route('login'));
        $this->post(route('account.links.store'), $this->payload($county))->assertRedirect(route('login'));
    }

    public function test_user_can_open_the_submission_form(): void
    {
        $this->actingAs($this->submitter())
            ->get(route('account.links.create'))
            ->assertOk()
            ->assertSee('Add a link or page')
            ->assertSee('Submit for administrator review');
    }

    public function test_user_can_submit_a_link_and_it_starts_as_pending(): void
    {
        $county = $this->county();
        $user = $this->submitter();

        $this->actingAs($user)
            ->post(route('account.links.store'), $this->payload($county))
            ->assertRedirect(route('account.links.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('resource_links', [
            'user_id' => $user->id,
            'county_id' => $county->id,
            'platform' => 'facebook_group',
            'url' => 'https://www.facebook.com/share/g/1UqwN8Sw26/',
            'approval_status' => ResourceLink::STATUS_PENDING,
            'followers' => 12500,
        ]);
    }

    public function test_submission_requires_a_county_and_a_supported_platform(): void
    {
        $county = $this->county();

        $this->actingAs($this->submitter())
            ->post(route('account.links.store'), $this->payload($county, [
                'county_id' => '',
                'platform' => 'myspace',
            ]))
            ->assertSessionHasErrors(['county_id', 'platform']);

        $this->assertDatabaseCount('resource_links', 0);
    }

    public function test_submission_rejects_an_unapproved_candidate(): void
    {
        $county = $this->county();
        $candidate = $this->candidate('Pending Person', 'Nyandarua', ['approval_status' => 'pending']);

        $this->actingAs($this->submitter())
            ->post(route('account.links.store'), $this->payload($county, ['candidate_id' => $candidate->id]))
            ->assertSessionHasErrors('candidate_id');

        $this->assertDatabaseCount('resource_links', 0);
    }

    public function test_user_sees_only_their_own_submissions_with_status(): void
    {
        $county = $this->county();
        $mine = $this->submitter();
        $theirs = $this->submitter('other.person@example.com');

        $this->actingAs($mine)->post(route('account.links.store'), $this->payload($county, [
            'comment' => 'My own group link',
        ]));
        $this->actingAs($theirs)->post(route('account.links.store'), $this->payload($county, [
            'comment' => 'Someone else group link',
        ]));

        $this->actingAs($mine)
            ->get(route('account.links.index'))
            ->assertOk()
            ->assertSee('My own group link')
            ->assertDontSee('Someone else group link')
            ->assertSee('Pending review');
    }

    public function test_pending_link_is_not_public(): void
    {
        $county = $this->county();

        $this->actingAs($this->submitter())->post(route('account.links.store'), $this->payload($county));

        $this->get(route('county.show', $county->slug))->assertOk()->assertDontSee('facebook.com/share/g/1UqwN8Sw26/');
    }

    public function test_admin_can_approve_a_link_and_it_becomes_public(): void
    {
        $county = $this->county();

        $this->actingAs($this->submitter())->post(route('account.links.store'), $this->payload($county));

        $link = ResourceLink::firstOrFail();

        $this->actingAs($this->admin())
            ->from(route('links.index'))
            ->patch(route('links.approval', $link), ['status' => 'approved'])
            ->assertRedirect(route('links.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('resource_links', [
            'id' => $link->id,
            'approval_status' => ResourceLink::STATUS_APPROVED,
        ]);
        $this->assertNotNull($link->fresh()->reviewed_at);

        $this->get(route('county.show', $county->slug))
            ->assertOk()
            ->assertSee('facebook.com/share/g/1UqwN8Sw26/');
    }

    public function test_admin_can_reject_a_link(): void
    {
        $county = $this->county();

        $this->actingAs($this->submitter())->post(route('account.links.store'), $this->payload($county));

        $link = ResourceLink::firstOrFail();

        $this->actingAs($this->admin())
            ->from(route('links.index'))
            ->patch(route('links.approval', $link), ['status' => 'rejected'])
            ->assertRedirect(route('links.index'));

        $this->assertDatabaseHas('resource_links', [
            'id' => $link->id,
            'approval_status' => ResourceLink::STATUS_REJECTED,
        ]);

        $this->get(route('county.show', $county->slug))->assertDontSee('facebook.com/share/g/1UqwN8Sw26/');
    }

    public function test_admin_can_filter_the_link_listing_by_status(): void
    {
        $county = $this->county();

        $this->actingAs($this->submitter())->post(route('account.links.store'), $this->payload($county));
        $this->actingAs($this->admin())
            ->from(route('links.index'))
            ->patch(route('links.approval', ResourceLink::firstOrFail()), ['status' => 'approved']);

        $this->actingAs($this->admin())
            ->get(route('links.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('No links have been submitted yet.');
    }

    public function test_admin_can_delete_a_link(): void
    {
        $county = $this->county();

        $this->actingAs($this->submitter())->post(route('account.links.store'), $this->payload($county));

        $link = ResourceLink::firstOrFail();

        $this->actingAs($this->admin())
            ->deleteJson(route('links.destroy', $link))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('resource_links', ['id' => $link->id]);
    }

    public function test_county_link_appears_on_every_aspirant_in_that_county(): void
    {
        $county = $this->county();
        $first = $this->candidate('Alice Wanjiru', 'Nyandarua');
        $second = $this->candidate('Brian Kamau', 'Nyandarua County');
        $elsewhere = $this->candidate('Carol Achieng', 'Nakuru');

        $this->actingAs($this->submitter())->post(route('account.links.store'), $this->payload($county));
        $this->actingAs($this->admin())
            ->from(route('links.index'))
            ->patch(route('links.approval', ResourceLink::firstOrFail()), ['status' => 'approved']);

        foreach ([$first, $second] as $candidate) {
            $this->get(route('aspirants.show', $candidate->slug))
                ->assertOk()
                ->assertSee('facebook.com/share/g/1UqwN8Sw26/');
        }

        $this->get(route('aspirants.show', $elsewhere->slug))
            ->assertOk()
            ->assertDontSee('facebook.com/share/g/1UqwN8Sw26/');
    }

    /**
     * County is a required field, so a link is always county-wide. Targeting an
     * aspirant therefore adds that profile to the county it already covered.
     * The test points the aspirant at a different county to prove the targeting
     * itself is exclusive.
     */
    public function test_link_targeted_at_one_aspirant_reaches_that_profile_and_stays_exclusive_elsewhere(): void
    {
        $county = $this->county();
        $target = $this->candidate('Alice Wanjiru', 'Nakuru');
        $otherInTargetCounty = $this->candidate('Brian Kamau', 'Nakuru');
        $countyMember = $this->candidate('Carol Achieng', 'Nyandarua');

        $this->actingAs($this->submitter())->post(route('account.links.store'), $this->payload($county, [
            'url' => 'https://www.facebook.com/groups/target-only/',
            'candidate_id' => $target->id,
        ]));
        $this->actingAs($this->admin())
            ->from(route('links.index'))
            ->patch(route('links.approval', ResourceLink::firstOrFail()), ['status' => 'approved']);

        $this->get(route('aspirants.show', $target->slug))
            ->assertOk()
            ->assertSee('facebook.com/groups/target-only/');

        $this->get(route('aspirants.show', $countyMember->slug))
            ->assertOk()
            ->assertSee('facebook.com/groups/target-only/');

        $this->get(route('aspirants.show', $otherInTargetCounty->slug))
            ->assertOk()
            ->assertDontSee('facebook.com/groups/target-only/');
    }

    public function test_party_link_appears_on_every_aspirant_of_that_party(): void
    {
        $county = $this->county();
        $party = PoliticalParty::create([
            'name' => 'Jubilee Party',
            'slug' => 'jubilee-party',
            'content' => 'A test party.',
        ]);

        $member = $this->candidate('Alice Wanjiru', 'Nakuru', ['political_party_id' => $party->id]);
        $outsider = $this->candidate('Brian Kamau', 'Nakuru');

        $this->actingAs($this->submitter())->post(route('account.links.store'), $this->payload($county, [
            'url' => 'https://www.facebook.com/groups/jubilee/',
            'political_party_id' => $party->id,
        ]));
        $this->actingAs($this->admin())
            ->from(route('links.index'))
            ->patch(route('links.approval', ResourceLink::firstOrFail()), ['status' => 'approved']);

        $this->get(route('aspirants.show', $member->slug))
            ->assertOk()
            ->assertSee('facebook.com/groups/jubilee/');

        $this->get(route('aspirants.show', $outsider->slug))
            ->assertOk()
            ->assertDontSee('facebook.com/groups/jubilee/');
    }

    public function test_county_page_lists_its_aspirants_and_constituencies(): void
    {
        $county = $this->county();
        $constituency = $this->constituency($county);
        $aspirant = $this->candidate('Alice Wanjiru', 'Nyandarua', ['constituency' => $constituency->name]);

        $this->get(route('county.show', $county->slug))
            ->assertOk()
            ->assertSee($county->name)
            ->assertSee($constituency->name)
            ->assertSee($aspirant->name);
    }

    public function test_county_page_resolves_by_slug(): void
    {
        $county = $this->county();

        $this->get('/counties/'.$county->slug)
            ->assertOk()
            ->assertSee($county->name);
    }

    public function test_aspirant_profile_links_to_its_county_page(): void
    {
        $county = $this->county();
        $candidate = $this->candidate('Alice Wanjiru', 'Nyandarua');

        $this->get(route('aspirants.show', $candidate->slug))
            ->assertOk()
            ->assertSee(route('county.show', $county->slug), false);
    }

    public function test_aspirant_profile_still_links_to_county_page_for_variant_names(): void
    {
        $county = $this->county();
        $candidate = $this->candidate('Alice Wanjiru', 'Nyandarua County');

        $this->get(route('aspirants.show', $candidate->slug))
            ->assertOk()
            ->assertSee(route('county.show', $county->slug), false);
    }

    public function test_aspirant_index_county_group_links_to_the_county_page(): void
    {
        $county = $this->county();
        $candidate = $this->candidate('Alice Wanjiru', 'Nyandarua');

        // With a position filter and no county, the index browses by county, so
        // the county cards are the entry point to the new county pages.
        $this->get(route('aspirants.public', ['position' => $candidate->position_id]))
            ->assertOk()
            ->assertSee(route('county.show', $county->slug), false);
    }
}
