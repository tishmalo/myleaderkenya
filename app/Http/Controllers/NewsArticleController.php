<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\Category;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsArticleController extends Controller
{
    public function index()
{
    $query = NewsArticle::with('author', 'categories', 'candidates');

    // Filter by category
    if ($categorySlug = request('category')) {
        $query->whereHas('categories', function($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    $articles = $query->latest()->paginate(15);

    // Get all categories for filter buttons
    $categories = Category::orderBy('name')->get();

    return view('news.index', compact('articles', 'categories'));
}

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $candidates = Candidate::orderBy('name')->get();
        return view('news.create', compact('categories', 'candidates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'excerpt'         => 'nullable|string',
            'content'         => 'required|string',
            'featured_image'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video_url'       => 'nullable|url',
            'categories'      => 'nullable|array',
            'categories.*'    => 'exists:categories,id',
            'candidates'      => 'nullable|array',
            'candidates.*'    => 'exists:candidates,id',
            'status'          => 'required|in:draft,published',
        ]);

        $data = $request->except(['featured_image', 'categories', 'candidates']);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('news', 'public');
        }

        $data['author_id'] = auth()->id();
        $data['slug'] = Str::slug($request->title);

        if ($request->status === 'published') {
            $data['published_at'] = now();
        }

        $article = NewsArticle::create($data);

        // Sync Categories
        if ($request->has('categories')) {
            $article->categories()->sync($request->categories);
        }

        // Sync Tagged Candidates (Aspirants)
        if ($request->has('candidates')) {
            $article->candidates()->sync($request->candidates);
        }

        return redirect()->route('news.index')
                         ->with('success', 'News article created successfully!');
    }

    public function edit(NewsArticle $news)
    {
        $categories = Category::orderBy('name')->get();
        $candidates = Candidate::orderBy('name')->get();
        return view('news.edit', compact('news', 'categories', 'candidates'));
    }

    public function update(Request $request, NewsArticle $news)
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'excerpt'         => 'nullable|string',
            'content'         => 'required|string',
            'featured_image'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video_url'       => 'nullable|url',
            'categories'      => 'nullable|array',
            'categories.*'    => 'exists:categories,id',
            'candidates'      => 'nullable|array',
            'candidates.*'    => 'exists:candidates,id',
            'status'          => 'required|in:draft,published',
        ]);

        $data = $request->except(['featured_image', 'categories', 'candidates']);

        if ($request->hasFile('featured_image')) {
            if ($news->featured_image) {
                Storage::disk('public')->delete($news->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('news', 'public');
        }

        $data['slug'] = Str::slug($request->title);

        if ($request->status === 'published' && !$news->published_at) {
            $data['published_at'] = now();
        }

        $news->update($data);

        // Sync Categories & Candidates
        if ($request->has('categories')) {
            $news->categories()->sync($request->categories);
        } else {
            $news->categories()->detach();
        }

        if ($request->has('candidates')) {
            $news->candidates()->sync($request->candidates);
        } else {
            $news->candidates()->detach();
        }

        return redirect()->route('news.index')
                         ->with('success', 'News article updated successfully!');
    }

    public function destroy(NewsArticle $news)
    {
        if ($news->featured_image) {
            Storage::disk('public')->delete($news->featured_image);
        }
        $news->delete();

        return response()->json([
            'success' => true,
            'message' => 'Article deleted successfully.'
        ]);
    }

    // Add these methods to NewsArticleController

public function publicIndex()
{
    $query = NewsArticle::with('author', 'categories', 'candidates')
                        ->where('status', 'published');

    // Category filter
    if ($slug = request('category')) {
        $query->whereHas('categories', fn($q) => $q->where('slug', $slug));
    }

    // Search
    if ($search = request('search')) {
        $query->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
    }

    $articles = $query->latest()->paginate(12);

    $categories = \App\Models\Category::orderBy('name')->get();

    return view('news.public.index', compact('articles', 'categories'));
}

public function publicShow($slug)
{
    $article = NewsArticle::with('author', 'categories', 'candidates')
                          ->where('slug', $slug)
                          ->where('status', 'published')
                          ->firstOrFail();

    return view('news.public.show', compact('article'));
}
}