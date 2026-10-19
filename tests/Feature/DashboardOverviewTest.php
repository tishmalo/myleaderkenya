<?php

namespace Tests\Feature;

use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\County;
use App\Models\NewsArticle;
use App\Models\PoliticalParty;
use App\Models\Poll;
use App\Models\Position;
use App\Models\ResourceLink;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin dashboard used to be all voters. It now reports the aspirant and
 * public content side too: how many governors, senators, links and so on.
 */
class DashboardOverviewTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);
    }

    private function aspirant(string $name, string $positionName, string $status = 'approved'): Candidate
    {
        return Candidate::create([
            'name' => $name,
            'position_id' => Position::firstOrCreate(['name' => $positionName], ['description' => $positionName])->id,
            'country' => 'Kenya',
            'county' => 'Nairobi',
            'approval_status' => $status,
        ]);
    }

    private function county(): County
    {
        $bloc = Bloc::firstOrCreate(['name' => 'Central'], ['tribes' => []]);

        return County::create(['name' => 'Nairobi', 'bloc_id' => $bloc->id]);
    }

    public function test_the_dashboard_reports_aspirants_by_position(): void
    {
        $this->aspirant('Governor One', 'Governor');
        $this->aspirant('Governor Two', 'Governor');
        $this->aspirant('Senator One', 'Senator');
        $this->aspirant('Pending Senator', 'Senator', 'pending');

        $response = $this->actingAs($this->admin())->get(route('dashboard'))->assertOk();

        $response->assertSee('Aspirants by Position');
        $response->assertSee('Governor');
        $response->assertSee('Senator');

        // 3 approved aspirants overall, 1 waiting for approval.
        $response->assertSee('3');
        $response->assertSee('awaiting approval');
    }

    public function test_the_dashboard_reports_links_parties_polls_and_stories(): void
    {
        $county = $this->county();

        ResourceLink::create([
            'platform' => 'facebook_group',
            'title' => 'Nairobi group',
            'url' => 'https://www.facebook.com/groups/nairobi',
            'county_id' => $county->id,
            'approval_status' => ResourceLink::STATUS_APPROVED,
        ]);

        ResourceLink::create([
            'platform' => 'facebook_group',
            'title' => 'Pending group',
            'url' => 'https://www.facebook.com/groups/pending',
            'county_id' => $county->id,
            'approval_status' => ResourceLink::STATUS_PENDING,
        ]);

        ResourceLink::create([
            'platform' => 'facebook_page',
            'title' => 'National page',
            'url' => 'https://www.facebook.com/national',
            'county_id' => null,
            'approval_status' => ResourceLink::STATUS_APPROVED,
        ]);

        PoliticalParty::create([
            'name' => 'Orange Democratic Movement',
            'abbreviation' => 'ODM',
            'content' => 'Party content',
            'status' => 'published',
        ]);

        $author = User::factory()->create(['username' => 'news-desk']);

        NewsArticle::create([
            'title' => 'A published story',
            'slug' => 'a-published-story',
            'content' => 'Body',
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        Poll::create([
            'question' => 'Who should be governor?',
            'slug' => 'who-governor',
            'poll_type' => Poll::TYPE_WORDS,
            'status' => Poll::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'created_by' => $author->id,
        ]);

        $response = $this->actingAs($this->admin())->get(route('dashboard'))->assertOk();

        $response->assertSee('Links by County');
        $response->assertSee('Nairobi');
        $response->assertSee('awaiting review');
        $response->assertSee('Political Parties');
        $response->assertSee('Polls');
        $response->assertSee('Stories published');
    }

    public function test_the_dashboard_still_shows_when_nothing_has_been_added(): void
    {
        $this->actingAs($this->admin())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total Users')
            ->assertSee('Aspirants')
            ->assertSee('No aspirants have been added yet.')
            ->assertSee('No approved links yet.');
    }
}
