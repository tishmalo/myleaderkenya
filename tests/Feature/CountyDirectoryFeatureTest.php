<?php

namespace Tests\Feature;

use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\Constituency;
use App\Models\County;
use App\Models\Position;
use App\Models\ResourceLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountyDirectoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function submitter(): User
    {
        return User::factory()->create([
            'name' => 'Jane Citizen',
            'email' => 'jane.citizen@example.com',
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

    private function position(string $name, int $sortOrder): Position
    {
        return Position::firstOrCreate(
            ['name' => $name],
            ['description' => $name, 'sort_order' => $sortOrder],
        );
    }

    private function candidate(string $name, Position $position, string $county, array $overrides = []): Candidate
    {
        return Candidate::create(array_merge([
            'name' => $name,
            'position_id' => $position->id,
            'country' => 'Kenya',
            'county' => $county,
            'approval_status' => 'approved',
        ], $overrides));
    }

    public function test_public_can_open_the_counties_directory(): void
    {
        $nyandarua = $this->county('Nyandarua');
        $nakuru = $this->county('Nakuru');

        $this->get(route('counties.public'))
            ->assertOk()
            ->assertSee('Counties')
            ->assertSee($nyandarua->name)
            ->assertSee($nakuru->name)
            ->assertSee(route('county.show', $nyandarua), false)
            ->assertSee(route('county.show', $nakuru), false);
    }

    public function test_voter_menu_links_to_the_counties_directory(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee(route('counties.public'), false);
    }

    public function test_admin_counties_live_under_the_admin_prefix(): void
    {
        $this->actingAs($this->admin())
            ->get(route('counties.index'))
            ->assertOk();

        $this->get('/counties/create')->assertNotFound();
    }

    public function test_county_page_groups_aspirants_by_position_in_order(): void
    {
        $county = $this->county();
        $governor = $this->position('Governor', 10);
        $mp = $this->position('Member of Parliament', 30);
        $mca = $this->position('Member of County Assembly', 40);
        $senator = $this->position('Senator', 20);

        $this->candidate('Grace Governor', $governor, 'Nyandarua');
        $this->candidate('Moses Mp', $mp, 'Nyandarua');
        $this->candidate('Mary Mca', $mca, 'Nyandarua');
        $this->candidate('Nancy Nakuru', $governor, 'Nakuru');

        $response = $this->get(route('county.show', $county->slug))->assertOk();

        $response->assertSeeInOrder(['Governor', 'Member of Parliament', 'Member of County Assembly']);
        $response->assertSee('Grace Governor');
        $response->assertDontSee('aria-labelledby="position-'.$senator->id.'"', false);
        $response->assertDontSee('Nancy Nakuru');
    }

    public function test_county_page_paginates_each_position_group_independently(): void
    {
        $county = $this->county();
        $governor = $this->position('Governor', 10);
        $mp = $this->position('Member of Parliament', 30);

        $this->candidate('Grace Governor', $governor, 'Nyandarua');

        for ($i = 1; $i <= 13; $i++) {
            $this->candidate(sprintf('Mp Number %02d', $i), $mp, 'Nyandarua');
        }

        $this->get(route('county.show', $county->slug))
            ->assertOk()
            ->assertSee('Mp Number 01')
            ->assertDontSee('Mp Number 13')
            ->assertSee('Grace Governor');

        $this->get(route('county.show', $county->slug).'?position_'.$mp->id.'_page=2')
            ->assertOk()
            ->assertSee('Mp Number 13')
            ->assertSee('Grace Governor');
    }

    public function test_county_page_shows_links_rail_constituencies_and_total(): void
    {
        $county = $this->county();
        $governor = $this->position('Governor', 10);
        $this->candidate('Grace Governor', $governor, 'Nyandarua');
        Constituency::create(['name' => 'Mathi East', 'county_id' => $county->id]);

        ResourceLink::create([
            'user_id' => $this->submitter()->id,
            'platform' => 'facebook_group',
            'title' => 'Nyandarua County Announcements',
            'url' => 'https://www.facebook.com/share/g/1UqwN8Sw26/',
            'county_id' => $county->id,
            'approval_status' => ResourceLink::STATUS_APPROVED,
        ]);

        $this->get(route('county.show', $county->slug))
            ->assertOk()
            ->assertSee('Aspirants in Nyandarua')
            ->assertSee('Nyandarua County Announcements')
            ->assertSee('Mathi East')
            ->assertSee('Featured Video');
    }
}
