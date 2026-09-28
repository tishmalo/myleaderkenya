<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsArticleCommentsTest extends TestCase
{
    use RefreshDatabase;

    private function article(array $overrides = []): NewsArticle
    {
        $author = User::factory()->create(['role' => 'admin']);

        return NewsArticle::create(array_merge([
            'title' => 'County budget review',
            'slug' => 'county-budget-review',
            'content' => 'The full story body.',
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now(),
        ], $overrides));
    }

    public function test_only_approved_comments_are_shown_to_visitors(): void
    {
        $article = $this->article();

        $article->comments()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'Approved and visible.',
            'status' => 'approved',
        ]);

        $article->comments()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'Pending and hidden.',
            'status' => 'pending',
        ]);

        $response = $this->get(route('news.public.show', $article->slug));

        $response->assertOk()
            ->assertSee('Approved and visible.')
            ->assertDontSee('Pending and hidden.')
            ->assertSee('1 comment', escape: false);
    }

    public function test_guests_are_prompted_to_log_in_instead_of_seeing_a_form(): void
    {
        $article = $this->article();

        $this->get(route('news.public.show', $article->slug))
            ->assertOk()
            ->assertSee('to leave a comment')
            ->assertDontSee('name="body"', escape: false);
    }

    public function test_authenticated_member_comment_is_stored_as_pending(): void
    {
        $article = $this->article();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('news.comments.store', $article->slug), ['body' => 'My take on this story.']);

        $response->assertRedirect();
        $response->assertSessionHas('comment_notice');

        $this->assertDatabaseHas('news_article_comments', [
            'news_article_id' => $article->id,
            'user_id' => $user->id,
            'body' => 'My take on this story.',
            'status' => 'pending',
        ]);
    }

    public function test_comment_body_is_trimmed_and_validated(): void
    {
        $article = $this->article();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('news.comments.store', $article->slug), ['body' => '   Padded comment   '])
            ->assertRedirect();

        $this->assertDatabaseHas('news_article_comments', ['body' => 'Padded comment']);

        $this->actingAs($user)
            ->from(route('news.public.show', $article->slug))
            ->post(route('news.comments.store', $article->slug), ['body' => 'x'])
            ->assertSessionHasErrors('body');
    }

    public function test_guests_cannot_submit_comments(): void
    {
        $article = $this->article();

        $this->post(route('news.comments.store', $article->slug), ['body' => 'Sneaky comment'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('news_article_comments', 0);
    }

    public function test_comment_cannot_be_posted_to_draft_or_missing_articles(): void
    {
        $user = User::factory()->create();
        $draft = $this->article(['status' => 'draft', 'slug' => 'draft-story']);

        $this->actingAs($user)
            ->post(route('news.comments.store', $draft->slug), ['body' => 'Hidden story comment'])
            ->assertNotFound();

        $this->actingAs($user)
            ->post(route('news.comments.store', 'no-such-article'), ['body' => 'Missing article'])
            ->assertNotFound();

        $this->assertDatabaseCount('news_article_comments', 0);
    }

    public function test_sidebar_lists_categories_with_published_article_counts(): void
    {
        $tag = Tag::firstOrCreate(['slug' => 'politics'], ['name' => 'Politics']);

        $published = $this->article(['slug' => 'published-one']);
        $published->tags()->sync([$tag->id]);
        $this->article(['slug' => 'published-two'])->tags()->sync([$tag->id]);

        $draft = $this->article(['slug' => 'draft-one', 'status' => 'draft']);
        $draft->tags()->sync([$tag->id]);

        $unused = Tag::firstOrCreate(['slug' => 'smoke-sport'], ['name' => 'Smoke Sport']);

        $this->get(route('news.public.show', $published->slug))
            ->assertOk()
            ->assertSee('Categories')
            ->assertSee($tag->name)
            ->assertSee(route('news.public', ['tag' => $tag->slug]), escape: false)
            ->assertDontSee(route('news.public', ['tag' => $unused->slug]), escape: false);
    }

    public function test_admin_can_approve_and_reject_comments(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $article = $this->article();
        $comment = $article->comments()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'Needs review.',
        ]);

        $this->actingAs($admin)
            ->put(route('news-comments.update', $comment), ['status' => 'approved'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('news_article_comments', [
            'id' => $comment->id,
            'status' => 'approved',
            'moderated_by' => $admin->id,
        ]);
        $this->assertNotNull($comment->fresh()->moderated_at);

        $this->actingAs($admin)
            ->putJson(route('news-comments.update', $comment), ['status' => 'nonsense'])
            ->assertStatus(422);

        $this->get(route('news.public.show', $article->slug))
            ->assertSee('Needs review.');
    }

    public function test_admin_moderation_queue_is_permission_gated(): void
    {
        // A profile-complete non-admin gets past the profile gate, then is refused by the admin gate.
        $member = User::factory()->create([
            'role' => 'user',
            'gender' => 'male',
            'year_of_birth' => 1990,
            'county' => 'Nairobi',
            'constituency' => 'Westlands',
            'ward' => 'Kitisuru',
            'country_of_residence' => 'Kenya',
        ]);

        // Browser request: the admin middleware redirects non-admins to the landing page.
        $this->actingAs($member)
            ->get(route('news-comments.index'))
            ->assertRedirect(route('landing'));

        // JSON request: it answers 403 instead.
        $this->actingAs($member)
            ->getJson(route('news-comments.index'))
            ->assertForbidden();

        // Guests are rejected outright by the permission middleware.
        $this->getJson(route('news-comments.index'))
            ->assertForbidden();
    }

    public function test_admin_can_delete_a_comment(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $article = $this->article();
        $comment = $article->comments()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'Delete me.',
        ]);

        $this->actingAs($admin)
            ->delete(route('news-comments.destroy', $comment))
            ->assertOk();

        $this->assertDatabaseMissing('news_article_comments', ['id' => $comment->id]);
    }

    public function test_news_admin_index_shows_pending_comment_count(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $article = $this->article();

        $article->comments()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'Awaiting review.',
        ]);
        $article->comments()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'Already approved.',
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get(route('news.index'))
            ->assertOk()
            ->assertSee(route('news-comments.index'), escape: false)
            ->assertSee('bg-orange-500/20 text-orange-400 text-xs">1<', escape: false);
    }

    public function test_campaign_videos_appear_on_public_pages_and_the_promo_on_the_homepage(): void
    {
        $article = $this->article();

        // The homepage carries the single featured promo video.
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('data-vs-video', escape: false)
            ->assertSee('SNAMMJbnSFo', escape: false)
            ->assertDontSee('M5arkEcnuy4', escape: false);

        $this->get(route('news.public.show', $article->slug))
            ->assertOk()
            ->assertSee('data-vs-video', escape: false)
            ->assertSee('SNAMMJbnSFo', escape: false)
            ->assertSee('M5arkEcnuy4', escape: false);
    }

    public function test_deleting_an_article_removes_its_comments(): void
    {
        $article = $this->article();
        $article->comments()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'Doomed comment.',
        ]);

        $article->delete();

        $this->assertDatabaseCount('news_article_comments', 0);
    }
}
