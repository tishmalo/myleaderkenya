<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SearchUserNewsCandidatesRequest;
use App\Http\Requests\Web\StoreUserNewsArticleRequest;
use App\Models\Candidate;
use App\Services\Web\AspirantWorkspaceService;
use App\Services\Web\UserNewsArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AspirantNewsArticleController extends Controller
{
    public function __construct(
        private UserNewsArticleService $news,
        private AspirantWorkspaceService $workspaces,
    ) {}

    public function index(Request $request): View
    {
        $candidate = $this->candidate($request);

        return view('aspirants.news.index', [
            'candidate' => $candidate,
            'articles' => $this->news->listForCandidate($candidate),
        ]);
    }

    public function create(Request $request): View
    {
        $candidate = $this->candidate($request);
        $selected = array_merge([$candidate->id], (array) $request->old('candidates', []));

        return view('aspirants.news.create', array_merge(
            ['candidate' => $candidate],
            $this->news->formData($selected),
        ));
    }

    public function searchCandidates(SearchUserNewsCandidatesRequest $request): JsonResponse
    {
        return response()->json([
            'results' => $this->news->searchCandidates($request->validated('q'))->values(),
        ]);
    }

    public function store(StoreUserNewsArticleRequest $request): RedirectResponse
    {
        $candidate = $this->candidate($request);

        $this->news->submitForCandidate(
            $request->user(),
            $candidate,
            $request->validated(),
            $request->file('featured_image'),
        );

        return redirect()->route('aspirant.news.index')
            ->with('success', 'The campaign article was submitted for administrator review.');
    }

    private function candidate(Request $request): Candidate
    {
        return $this->workspaces->candidateForUser($request->user())
            ?? abort(404, 'No active aspirant workspace was found.');
    }
}
