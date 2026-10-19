<?php

namespace Tests\Feature;

use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\County;
use App\Models\PoliticalParty;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Position;
use App\Models\ResourceLink;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pages and links follow the aspirants they belong to: under each position list
 * on a county page, on the aspirant profile, and on the polls that name them.
 */
class AspirantLinkPlacementTest extends TestCase
{
    use RefreshDatabase;

    private County $county;

    private ?User $voterUser = null;

    protected function setUp(): void
    {
        parent::setUp();

        $bloc = Bloc::firstOrCreate(['name' => 'Western'], ['tribes' => []]);
        $this->county = County::create(['name' => 'Bungoma', 'bloc_id' => $bloc->id]);
    }

    private function candidate(string $name, string $positionName, array $attributes = []): Candidate
    {
        return Candidate::create(array_merge([
            'name' => $name,
            'position_id' => Position::firstOrCreate(['name' => $positionName], ['description' => $positionName])->id,
            'country' => 'Kenya',
            'county' => 'Bungoma',
            'approval_status' => 'approved',
        ], $attributes));
    }

    private function link(string $title, string $url, array $attributes = []): ResourceLink
    {
        return ResourceLink::create(array_merge([
            'platform' => 'facebook_page',
            'title' => $title,
            'url' => $url,
            'county_id' => $this->county->id,
            'approval_status' => ResourceLink::STATUS_APPROVED,
        ], $attributes));
    }

    private function voter(): User
    {
        return $this->voterUser ??= User::factory()->create([
            'name' => 'Verified Voter',
            'username' => 'verified-voter',
            'role' => 'user',
            'role_id' => Role::idFor(Role::USER),
            'is_voter' => true,
        ]);
    }

    public function test_a_county_page_features_links_under_each_position_list(): void
    {
        $governor = $this->candidate('Wycliffe Wangamati', 'Governor');
        $senator = $this->candidate('Moses Wetangula', 'Senator');

        $this->link('Wangamati Campaign Page', 'https://www.facebook.com/Wangamati', [
            'candidate_id' => $governor->id,
        ]);
        $this->link('Wetangula Campaign Page', 'https://www.facebook.com/Wetangula', [
            'candidate_id' => $senator->id,
        ]);

        $response = $this->actingAs($this->voter())
            ->get(route('county.show', $this->county))
            ->assertOk();

        $response->assertSee('Governor Pages &amp; Links', false);
        $response->assertSee('Senator Pages &amp; Links', false);
        $response->assertSee('https://www.facebook.com/Wangamati', false);
        $response->assertSee('https://www.facebook.com/Wetangula', false);

        // Each block sits after its own position heading.
        $html = (string) $response->getContent();
        $governorHeading = strpos($html, 'position-'.$governor->position_id);
        $governorLinks = strpos($html, 'Wangamati Campaign Page');
        $senatorLinks = strpos($html, 'Wetangula Campaign Page');

        $this->assertNotFalse($governorHeading);
        $this->assertGreaterThan($governorHeading, $governorLinks);
        $this->assertGreaterThan($governorLinks, $senatorLinks, 'Senator links follow the governor block.');
    }

    public function test_a_county_page_keeps_its_overall_links_block(): void
    {
        $this->candidate('Wycliffe Wangamati', 'Governor');
        $this->link('BUNGOMA TODAY', 'https://www.facebook.com/groups/bungoma');

        $this->actingAs($this->voter())
            ->get(route('county.show', $this->county))
            ->assertOk()
            ->assertSee('Bungoma Pages &amp; Links', false)
            ->assertSee('https://www.facebook.com/groups/bungoma', false);
    }

    public function test_an_aspirant_profile_features_their_links(): void
    {
        $governor = $this->candidate('Wycliffe Wangamati', 'Governor');
        $this->link('Wangamati Facebook', 'https://www.facebook.com/Wangamati', [
            'candidate_id' => $governor->id,
        ]);

        $this->actingAs($this->voter())
            ->get(route('aspirants.show', $governor))
            ->assertOk()
            ->assertSee('Wangamati Facebook')
            ->assertSee('https://www.facebook.com/Wangamati', false);
    }

    public function test_an_aspirant_profile_features_the_county_links_that_match_them(): void
    {
        $governor = $this->candidate('Wycliffe Wangamati', 'Governor');
        $outsider = $this->candidate('Kenneth Lusaka', 'Governor', ['county' => 'Kakamega']);

        // A county wide link with no narrower scope reaches every aspirant of
        // that county, and stays off another county's aspirant.
        $this->link('BUNGOMA TODAY', 'https://www.facebook.com/groups/bungoma');

        $this->actingAs($this->voter())
            ->get(route('aspirants.show', $governor))
            ->assertOk()
            ->assertSee('BUNGOMA TODAY')
            ->assertSee('https://www.facebook.com/groups/bungoma', false);

        $this->actingAs($this->voter())
            ->get(route('aspirants.show', $outsider))
            ->assertOk()
            ->assertDontSee('BUNGOMA TODAY');
    }

    public function test_a_poll_features_the_links_of_the_aspirants_it_names(): void
    {
        $governor = $this->candidate('Wycliffe Wangamati', 'Governor');
        $rival = $this->candidate('Kenneth Lusaka', 'Governor');

        $this->link('Wangamati Facebook', 'https://www.facebook.com/Wangamati', [
            'candidate_id' => $governor->id,
        ]);
        $this->link('Lusaka Facebook', 'https://www.facebook.com/Lusaka', [
            'candidate_id' => $rival->id,
        ]);

        $poll = Poll::create([
            'question' => 'Who should be Bungoma governor?',
            'slug' => 'bungoma-governor-race',
            'poll_type' => Poll::TYPE_POLITICAL,
            'status' => Poll::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'audience_scope' => Poll::AUDIENCE_COUNTY,
            'audience_county' => 'Bungoma',
            'created_by' => $this->voter()->id,
        ]);

        PollOption::create(['poll_id' => $poll->id, 'label' => 'Wangamati', 'candidate_id' => $governor->id, 'display_order' => 1]);
        PollOption::create(['poll_id' => $poll->id, 'label' => 'Lusaka', 'candidate_id' => $rival->id, 'display_order' => 2]);

        $this->actingAs($this->voter())
            ->get(route('poll.show', $poll->slug))
            ->assertOk()
            ->assertSee('Pages &amp; Links for these aspirants', false)
            ->assertSee('Wangamati Facebook')
            ->assertSee('Lusaka Facebook');
    }

    public function test_a_poll_without_aspirant_options_shows_no_links_block(): void
    {
        $poll = Poll::create([
            'question' => 'Should the county publish its budget?',
            'slug' => 'budget-question',
            'poll_type' => Poll::TYPE_WORDS,
            'status' => Poll::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'created_by' => $this->voter()->id,
        ]);

        PollOption::create(['poll_id' => $poll->id, 'label' => 'Yes', 'display_order' => 1]);
        PollOption::create(['poll_id' => $poll->id, 'label' => 'No', 'display_order' => 2]);

        $this->actingAs($this->voter())
            ->get(route('poll.show', $poll->slug))
            ->assertOk()
            ->assertDontSee('Pages &amp; Links for these aspirants', false);
    }

    public function test_the_links_stay_members_only_on_the_new_placements(): void
    {
        $governor = $this->candidate('Wycliffe Wangamati', 'Governor');
        $this->link('Wangamati Facebook', 'https://www.facebook.com/Wangamati', [
            'candidate_id' => $governor->id,
        ]);

        // A visitor sees the section, but not the destination.
        $this->get(route('aspirants.show', $governor))
            ->assertOk()
            ->assertSee('Wangamati Facebook')
            ->assertDontSee('https://www.facebook.com/Wangamati', false);
    }
}
