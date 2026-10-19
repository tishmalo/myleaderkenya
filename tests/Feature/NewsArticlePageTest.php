<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\NewsArticleComment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The article page: newest comment first, and a rail that no longer squeezes
 * the story into a narrow column.
 */
class NewsArticlePageTest extends TestCase
{
    use RefreshDatabase;

    private function article(): NewsArticle
    {
        $author = User::factory()->create(['name' => 'News Desk', 'username' => 'news-desk']);

        return NewsArticle::create([
            'title' => 'Sifuna gambit',
            'slug' => 'sifunas-gambit',
            'content' => '<p>Edwin Sifuna is now a general with a ticket.</p>',
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);
    }

    private function comment(NewsArticle $article, string $body, User $user, string $when): NewsArticleComment
    {
        $comment = NewsArticleComment::create([
            'news_article_id' => $article->id,
            'user_id' => $user->id,
            'body' => $body,
            'status' => NewsArticleComment::STATUS_APPROVED,
        ]);

        $comment->forceFill(['created_at' => $when])->saveQuietly();

        return $comment;
    }

    public function test_the_newest_comment_comes_first(): void
    {
        $article = $this->article();
        $reader = User::factory()->create(['name' => 'Reader One', 'username' => 'reader-one']);

        $this->comment($article, 'Oldest thought', $reader, now()->subDays(3)->toDateTimeString());
        $this->comment($article, 'Middle thought', $reader, now()->subDay()->toDateTimeString());
        $this->comment($article, 'Newest thought', $reader, now()->toDateTimeString());

        $this->get(route('news.public.show', $article->slug))
            ->assertOk()
            ->assertSeeInOrder([
                'Newest thought',
                'Middle thought',
                'Oldest thought',
            ]);
    }

    public function test_unapproved_comments_stay_hidden(): void
    {
        $article = $this->article();
        $reader = User::factory()->create(['name' => 'Reader One', 'username' => 'reader-one']);

        $this->comment($article, 'Approved thought', $reader, now()->toDateTimeString());

        NewsArticleComment::create([
            'news_article_id' => $article->id,
            'user_id' => $reader->id,
            'body' => 'Waiting for review',
            'status' => NewsArticleComment::STATUS_PENDING,
        ]);

        $this->get(route('news.public.show', $article->slug))
            ->assertOk()
            ->assertSee('Approved thought')
            ->assertDontSee('Waiting for review');
    }

    public function test_the_rail_takes_a_third_of_the_page_like_the_other_pages(): void
    {
        $article = $this->article();

        $html = (string) $this->get(route('news.public.show', $article->slug))->assertOk()->getContent();

        $this->assertStringContainsString('grid-template-columns: minmax(0, 7fr) minmax(0, 3fr);', $html);
        $this->assertStringNotContainsString('minmax(0, 1fr) 523px', $html);
    }
}
