<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsArticleStoreRequest;
use App\Http\Requests\Admin\NewsArticleUpdateRequest;
use App\Models\NewsArticle;
use App\Models\NewsArticleComment;
use App\Services\Admin\NewsArticleService;
use App\Services\SeoService;
use App\Services\Web\ViewTracker;
use Illuminate\Support\Str;

class NewsArticleController extends Controller
{
    public function __construct(
        private NewsArticleService $newsArticleService,
        private ViewTracker $viewTracker,
        private SeoService $seo,
    ) {}

    public function index()
    {
        $filters = request()->only(['tag']);
        $articles = $this->newsArticleService->getPaginatedArticles($filters);

        ['tags' => $tags] = $this->newsArticleService->getFormData();

        $pendingCommentCount = $this->newsArticleService->getPaginatedComments(
            ['status' => NewsArticleComment::STATUS_PENDING],
            1
        )->total();

        return view('news.index', compact('articles', 'tags', 'pendingCommentCount'));
    }

    public function create()
    {
        $data = $this->newsArticleService->getFormData();
        return view('news.create', $data);
    }

    public function publicShow($slug)
{
    $data = $this->newsArticleService->getPublicShowPageData($slug);
    $article = $data['article'];

    $this->viewTracker->track($article, request());


    $title = $article->title . ' | Kenya 2027 News';
    if (strlen($title) > 60) {
        $title = \Illuminate\Support\Str::limit($article->title, 55, '') . ' | 2027';
    }

    $description = $article->excerpt
        ?: \Illuminate\Support\Str::limit(strip_tags($article->content ?? ''), 155, '');

    $this->seo->setTitle($title)
        ->setDescription($description ?: 'Read the latest Kenya 2027 election news on My Leader Kenya.')
        ->setCanonical(route('news.public.show', $article->slug))
        ->setOg([
            'title' => $title,
            'description' => $description,
            'type' => 'article',
            'image' => $article->featured_image_url ?? asset('images/myleader.png'),
        ]);

    // NewsArticle schema
    $this->seo->addSchema([
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $article->title,
        'description' => $description,
        'url' => route('news.public.show', $article->slug),
        'datePublished' => $article->published_at?->toIso8601String() ?? $article->created_at->toIso8601String(),
        'dateModified' => $article->updated_at->toIso8601String(),
        'image' => $article->featured_image_url ?? asset('images/myleader.png'),
        'author' => [
            '@type' => 'Organization',
            'name' => 'My Leader Kenya',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'My Leader Kenya',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('images/myleader.png'),
            ],
        ],
    ]);

    return view('news.public.show', $data);
}

    public function store(NewsArticleStoreRequest $request)
    {
        $this->newsArticleService->createArticle(
            $request->except(['featured_image', 'tags', 'candidates', 'political_parties']),
            $request->file('featured_image'),
            $request->input('tags', []),
            $request->input('candidates', []),
            $request->input('political_parties', [])
        );

        return redirect()->route('news.index')
                         ->with('success', 'News article created successfully!');
    }

    public function edit(NewsArticle $news)
    {
        $data = $this->newsArticleService->getFormData();
        $data['news'] = $news->load('tags', 'candidates', 'politicalParties');
        return view('news.edit', $data);
    }

    public function update(NewsArticleUpdateRequest $request, NewsArticle $news)
    {
        $this->newsArticleService->updateArticle(
            $news,
            $request->except(['featured_image', 'tags', 'candidates', 'political_parties']),
            $request->file('featured_image'),
            $request->input('tags', []),
            $request->input('candidates', []),
            $request->input('political_parties', [])
        );

        return redirect()->route('news.index')
                         ->with('success', 'News article updated successfully!');
    }

    public function destroy(NewsArticle $news)
    {
        $this->newsArticleService->deleteArticle($news);

        return response()->json([
            'success' => true,
            'message' => 'Article deleted successfully.'
        ]);
    }

    public function publicIndex()
    {
        $filters = request()->only(['tag', 'search']);
        $filters['published_only'] = true;

        $articles = $this->newsArticleService->getPaginatedArticles($filters, 12);
        ['tags' => $tags] = $this->newsArticleService->getFormData();

        $this->seo->setTitle('Kenya 2027 Election News & Updates | My Leader Kenya')
            ->setDescription('Latest news, analysis and updates on Kenya 2027 aspirants, parties, polls and election developments. Stay informed on My Leader Kenya.')
            ->setCanonical(route('news.public'));

        return view('news.public.index', compact('articles', 'tags'));
    }


    
}



