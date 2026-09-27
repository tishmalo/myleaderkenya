<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsCommentStoreRequest;
use App\Models\NewsArticle;
use App\Services\Admin\NewsArticleService;

class NewsCommentController extends Controller
{
    public function __construct(
        private NewsArticleService $newsArticleService
    ) {}

    public function store(NewsCommentStoreRequest $request, string $slug)
    {
        $article = NewsArticle::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $this->newsArticleService->submitComment(
            $article,
            $request->user(),
            $request->validated('body'),
        );

        return back()->with(
            'comment_notice',
            'Thanks for your comment. It will appear once a moderator approves it.',
        );
    }
}
