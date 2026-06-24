<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsArticleStoreRequest;
use App\Http\Requests\Admin\NewsArticleUpdateRequest;
use App\Models\NewsArticle;
use App\Services\Admin\NewsArticleService;
use Illuminate\Http\Request;

class NewsArticleController extends Controller
{
    public function __construct(
        private NewsArticleService $newsArticleService
    ) {}

    public function index()
    {
        $filters = request()->only(['category']);
        $articles = $this->newsArticleService->getPaginatedArticles($filters);
        
        ['categories' => $categories] = $this->newsArticleService->getFormData();

        return view('news.index', compact('articles', 'categories'));
    }

    public function create()
    {
        $data = $this->newsArticleService->getFormData();
        return view('news.create', $data);
    }

    public function store(NewsArticleStoreRequest $request)
    {
        $this->newsArticleService->createArticle(
            $request->except(['featured_image', 'categories', 'candidates']),
            $request->file('featured_image'),
            $request->input('categories', []),
            $request->input('candidates', [])
        );

        return redirect()->route('news.index')
                         ->with('success', 'News article created successfully!');
    }

    public function edit(NewsArticle $news)
    {
        $data = $this->newsArticleService->getFormData();
        $data['news'] = $news;
        return view('news.edit', $data);
    }

    public function update(NewsArticleUpdateRequest $request, NewsArticle $news)
    {
        $this->newsArticleService->updateArticle(
            $news,
            $request->except(['featured_image', 'categories', 'candidates']),
            $request->file('featured_image'),
            $request->input('categories', []),
            $request->input('candidates', [])
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
        $filters = request()->only(['category', 'search']);
        $filters['published_only'] = true;

        $articles = $this->newsArticleService->getPaginatedArticles($filters, 12);
        
        ['categories' => $categories] = $this->newsArticleService->getFormData();

        return view('news.public.index', compact('articles', 'categories'));
    }

    public function publicShow($slug)
    {
        $article = $this->newsArticleService->getPublicShowData($slug);

        return view('news.public.show', compact('article'));
    }
}
