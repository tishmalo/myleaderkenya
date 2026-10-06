<?php

namespace Tests\Feature;

use App\Models\Bloc;
use App\Models\CampaignTool;
use App\Models\Candidate;
use App\Models\Coalition;
use App\Models\County;
use App\Models\Event;
use App\Models\NewsArticle;
use App\Models\PoliticalParty;
use App\Models\Poll;
use App\Models\Position;
use App\Models\User;
use App\Services\Web\SitemapService;
use App\Support\HomepageCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        HomepageCache::flush();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function county(string $name = 'Nyandarua'): County
    {
        $bloc = Bloc::firstOrCreate(['name' => 'Central'], ['tribes' => []]);

        return County::create(['name' => $name, 'bloc_id' => $bloc->id]);
    }

    private function candidate(string $name, string $county, string $approval = 'approved'): Candidate
    {
        $position = Position::firstOrCreate(['name' => 'Member of Parliament'], ['description' => 'MP']);

        return Candidate::create([
            'name' => $name,
            'position_id' => $position->id,
            'country' => 'Kenya',
            'county' => $county,
            'approval_status' => $approval,
        ]);
    }

    private function poll(string $question, string $slug, string $status = Poll::STATUS_ACTIVE): Poll
    {
        $poll = Poll::create([
            'question' => $question,
            'slug' => $slug,
            'poll_type' => Poll::TYPE_WORDS,
            'status' => $status,
            'ends_at' => now()->addDays(3),
            'reveal_results' => true,
            'created_by' => $this->admin()->id,
        ]);
        $poll->options()->create(['label' => 'Option A', 'display_order' => 0]);
        $poll->options()->create(['label' => 'Option B', 'display_order' => 1]);

        return $poll;
    }

    public function test_sitemap_index_lists_a_file_per_section(): void
    {
        $this->county();
        $this->candidate('Alice Wanjiru', 'Nyandarua');

        $response = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');

        foreach (['static', 'counties', 'aspirants'] as $section) {
            $response->assertSee('/sitemaps/'.$section.'.xml', false);
        }
    }

    public function test_aspirant_file_lists_approved_profiles_only(): void
    {
        $this->candidate('Alice Wanjiru', 'Nyandarua');
        $this->candidate('Pending Person', 'Nyandarua', 'pending');

        $response = $this->get('/sitemaps/aspirants.xml')->assertOk();

        $response->assertSee(route('aspirants.show', Candidate::where('name', 'Alice Wanjiru')->first()), false);
        $response->assertDontSee('Pending Person');
    }

    public function test_county_file_lists_the_directory_and_every_county(): void
    {
        $county = $this->county();

        $this->get('/sitemaps/counties.xml')
            ->assertOk()
            ->assertSee(route('counties.public'), false)
            ->assertSee(route('county.show', $county), false);
    }

    public function test_poll_file_lists_live_polls_only(): void
    {
        $live = $this->poll('Live question?', 'live-question');
        $this->poll('Draft question?', 'draft-question', Poll::STATUS_DRAFT);

        $response = $this->get('/sitemaps/polls.xml')->assertOk();

        $response->assertSee(route('poll.show', $live->slug), false);
        $response->assertDontSee('draft-question');
    }

    public function test_news_file_lists_published_articles_only(): void
    {
        $authorId = $this->admin()->id;

        NewsArticle::create(['title' => 'Published story', 'status' => 'published', 'content' => 'Hi.', 'author_id' => $authorId]);
        NewsArticle::create(['title' => 'Draft story', 'status' => 'draft', 'content' => 'Hi.', 'author_id' => $authorId]);

        $response = $this->get('/sitemaps/news.xml')->assertOk();

        $response->assertSee('published-story', false);
        $response->assertDontSee('draft-story');
    }

    public function test_event_file_lists_active_approved_events_only(): void
    {
        $open = Event::create([
            'title' => 'Open day', 'slug' => 'open-day', 'description' => 'Come one, come all.',
            'date' => now()->addWeek(), 'location' => 'Nairobi',
            'is_active' => true, 'approval_status' => Event::STATUS_APPROVED,
        ]);
        $pending = Event::create([
            'title' => 'Pending day', 'slug' => 'pending-day', 'description' => 'Soon.',
            'date' => now()->addWeek(), 'location' => 'Nairobi',
            'is_active' => true, 'approval_status' => Event::STATUS_PENDING,
        ]);

        $response = $this->get('/sitemaps/events.xml')->assertOk();

        $response->assertSee(route('events.show', $open), false);
        $response->assertDontSee(route('events.show', $pending), false);
    }

    public function test_party_and_coalition_files_list_published_records_only(): void
    {
        PoliticalParty::create(['name' => 'Open Party', 'slug' => 'open-party', 'status' => 'published', 'content' => 'Hi.']);
        PoliticalParty::create(['name' => 'Hidden Party', 'slug' => 'hidden-party', 'status' => 'draft', 'content' => 'Hi.']);
        Coalition::create(['name' => 'Open Pact', 'slug' => 'open-pact', 'status' => 'published', 'content' => 'Hi.']);
        Coalition::create(['name' => 'Hidden Pact', 'slug' => 'hidden-pact', 'status' => 'draft', 'content' => 'Hi.']);

        $this->get('/sitemaps/parties.xml')->assertOk()->assertSee('open-party', false)->assertDontSee('hidden-party');
        $this->get('/sitemaps/coalitions.xml')->assertOk()->assertSee('open-pact', false)->assertDontSee('hidden-pact');
    }

    public function test_unknown_sitemap_file_returns_404(): void
    {
        $this->get('/sitemaps/nope.xml')->assertNotFound();
    }

    public function test_guests_are_redirected_from_the_admin_sitemap_page(): void
    {
        $this->get(route('sitemap.admin'))->assertRedirect(route('login'));
        $this->post(route('sitemap.regenerate'))->assertRedirect(route('login'));
    }

    public function test_admin_can_open_the_sitemap_page_and_regenerate(): void
    {
        $this->county();
        $service = app(SitemapService::class);
        $before = $service->version();

        $this->actingAs($this->admin())
            ->get(route('sitemap.admin'))
            ->assertOk()
            ->assertSee('Sitemap')
            ->assertSee('/sitemap.xml', false);

        $this->actingAs($this->admin())
            ->post(route('sitemap.regenerate'))
            ->assertRedirect(route('sitemap.admin'))
            ->assertSessionHas('success');

        $this->assertSame($before + 1, $service->version());
    }

    public function test_regenerate_refreshes_the_cached_files(): void
    {
        $this->county('Nyandarua');

        $this->get('/sitemaps/counties.xml')->assertOk();

        $kiambu = $this->county('Kiambu');
        $kiambuUrl = route('county.show', $kiambu);

        // Still stale until regenerated.
        $this->get('/sitemaps/counties.xml')->assertOk()->assertDontSee($kiambuUrl, false);

        $this->actingAs($this->admin())->post(route('sitemap.regenerate'));

        $this->get('/sitemaps/counties.xml')->assertOk()->assertSee($kiambuUrl, false);
    }

    public function test_tools_file_lists_published_tools_only(): void
    {
        CampaignTool::create(['title' => 'Bulk SMS', 'status' => 'published', 'content' => 'Hi.']);
        CampaignTool::create(['title' => 'Secret Tool', 'status' => 'draft', 'content' => 'Hi.']);

        $response = $this->get('/sitemaps/tools.xml')->assertOk();

        $response->assertSee('bulk-sms', false);
        $response->assertDontSee('secret-tool');
    }
}
