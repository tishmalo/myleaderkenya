<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateNewsCommentStatusRequest;
use App\Models\NewsArticleComment;
use App\Services\Admin\NewsArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsCommentController extends Controller
{
    public function __construct(
        private NewsArticleService $newsArticleService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search']);

        $comments = $this->newsArticleService->getPaginatedComments($filters);

        return view('news.comments', compact('comments', 'filters'));
    }

    public function update(UpdateNewsCommentStatusRequest $request, NewsArticleComment $newsComment): JsonResponse
    {
        $status = $request->status();

        $this->newsArticleService->setCommentStatus(
            $newsComment,
            $status,
            $request->user(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Comment marked as '.$status.'.',
        ]);
    }

    public function destroy(NewsArticleComment $newsComment): JsonResponse
    {
        $this->newsArticleService->deleteComment($newsComment);

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully.',
        ]);
    }
}
