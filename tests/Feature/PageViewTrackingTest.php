<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\NewsArticle;
use App\Models\PoliticalParty;
use App\Models\Poll;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Views on the public content pages: counted per visitor per day, with staff
 * and crawlers ignored, and surfaced in the admin.
 */
class PageViewTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function article(): NewsArticle
    {
        $author = User::factory()->create(['name' => 'News Desk', 'username' => 'news-desk']);

        return NewsArticle::create([
            'title' => 'Helicopter incident raises questions after Ruto visit',
            'slug' => 'helicopter-incident',
            'content' => '<p>The visit raised questions.</p>',
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);
    }

    private function aspirant(): Candidate
    {
        return Candidate::create([
            'name' => 'Edwin Sifuna',
            'position_id' => Position::firstOrCreate(['name' => 'Senator'], ['description' => 'Senator'])->id,
            'country' => 'Kenya',
            'county' => 'Nairobi',
            'approval_status' => 'approved',
        ]);
    }

    private function poll(): Poll
    {
        return Poll::create([
            'question' => 'Who should be senator?',
            'slug' => 'senator-race',
            'poll_type' => Poll::TYPE_WORDS,
            'status' => Poll::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'created_by' => User::factory()->create(['username' => 'poll-author'])->id,
        ]);
    }

    private function party(): PoliticalParty
    {
        return PoliticalParty::create([
            'name' => 'Orange Democratic Movement',
            'abbreviation' => 'ODM',
            'content' => 'Party content',
            'status' => 'published',
        ]);
    }

    private function staff(): User
    {
        return User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);
    }

    private function visitorFrom(string $ip): self
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip]);
    }

    public function test_an_article_view_is_counted_and_shown(): void
    {
        $article = $this->article();

        $this->visitorFrom('10.1.0.1')
            ->get(route('news.public.show', $article->slug))
            ->assertOk()
            ->assertSee('1 views');

        $this->assertSame(1, $article->fresh()->views);
    }

    public function test_a_visitor_only_counts_once_a_day(): void
    {
        $article = $this->article();

        $this->visitorFrom('10.1.0.2')->get(route('news.public.show', $article->slug))->assertOk();
        $this->visitorFrom('10.1.0.2')->get(route('news.public.show', $article->slug))->assertOk();
        $this->visitorFrom('10.1.0.2')->get(route('news.public.show', $article->slug))->assertOk();

        $this->assertSame(1, $article->fresh()->views);
    }

    public function test_a_second_visitor_adds_another_view(): void
    {
        $article = $this->article();

        $this->visitorFrom('10.1.0.3')->get(route('news.public.show', $article->slug))->assertOk();
        $this->visitorFrom('10.1.0.4')->get(route('news.public.show', $article->slug))->assertOk();

        $this->assertSame(2, $article->fresh()->views);
    }

    public function test_crawlers_and_previews_are_ignored(): void
    {
        $article = $this->article();

        $this->visitorFrom('10.1.0.5')
            ->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1)')
            ->get(route('news.public.show', $article->slug))
            ->assertOk();

        $this->visitorFrom('10.1.0.6')
            ->withHeader('User-Agent', 'facebookexternalhit/1.1')
            ->get(route('news.public.show', $article->slug))
            ->assertOk();

        $this->assertSame(0, $article->fresh()->views);
    }

    public function test_staff_previews_are_ignored(): void
    {
        $article = $this->article();
        $staff = $this->staff();

        $this->actingAs($staff)
            ->visitorFrom('10.1.0.7')
            ->get(route('news.public.show', $article->slug))
            ->assertOk();

        $this->assertSame(0, $article->fresh()->views);
    }

    public function test_aspirant_poll_and_party_pages_are_counted_too(): void
    {
        $aspirant = $this->aspirant();
        $poll = $this->poll();
        $party = $this->party();

        $this->visitorFrom('10.1.0.8')->get(route('aspirants.show', $aspirant))->assertOk();
        $this->visitorFrom('10.1.0.8')->get(route('poll.show', $poll->slug))->assertOk();
        $this->visitorFrom('10.1.0.8')->get(route('parties.show', $party->slug))->assertOk();

        $this->assertSame(1, $aspirant->fresh()->views);
        $this->assertSame(1, $poll->fresh()->views);
        $this->assertSame(1, $party->fresh()->views);
    }

    public function test_the_counts_are_visible_on_the_public_pages(): void
    {
        $aspirant = $this->aspirant();
        $poll = $this->poll();
        $party = $this->party();

        $this->visitorFrom('10.1.0.9')->get(route('aspirants.show', $aspirant))->assertOk()->assertSee('Profile views');
        $this->visitorFrom('10.1.0.9')->get(route('poll.show', $poll->slug))->assertOk()->assertSee('1 views');
        $this->visitorFrom('10.1.0.9')->get(route('parties.show', $party->slug))->assertOk()->assertSee('1 views');
    }

    public function test_the_admin_sees_what_is_popular(): void
    {
        $article = $this->article();
        $aspirant = $this->aspirant();

        $article->forceFill(['views' => 42])->saveQuietly();
        $aspirant->forceFill(['views' => 7])->saveQuietly();

        $this->actingAs($this->staff())
            ->get(route('admin.popular'))
            ->assertOk()
            ->assertSee('Popular Content')
            ->assertSee('Helicopter incident raises questions after Ruto visit')
            ->assertSee('42')
            ->assertSee('Edwin Sifuna')
            ->assertSee('7');
    }

    public function test_popular_content_needs_the_public_content_permission(): void
    {
        $clerk = User::factory()->create([
            'name' => 'Clerk Kim',
            'username' => 'clerk-kim',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::ADMIN),
        ]);

        // The clerk role has no frontend.view permission by default.
        Role::query()->where('name', Role::ADMIN)->firstOrFail()
            ->permissions()->detach(\App\Models\Permission::query()->where('name', 'frontend.view')->pluck('id')->all());

        $this->actingAs($clerk->fresh())
            ->get(route('admin.popular'))
            ->assertForbidden();
    }
}
