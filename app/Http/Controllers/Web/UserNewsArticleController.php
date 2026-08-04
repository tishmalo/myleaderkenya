<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreUserNewsArticleRequest;
use App\Services\Web\UserNewsArticleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserNewsArticleController extends Controller
{
    public function __construct(private UserNewsArticleService $news) {}

    public function index(Request $request): View
    {
        return view('account.news.index', [
            'articles' => $this->news->listFor($request->user()),
        ]);
    }

    public function create(): View
    {
        return view('account.news.create', $this->news->formData());
    }

    public function store(StoreUserNewsArticleRequest $request): RedirectResponse
    {
        $this->news->submit(
            $request->user(),
            $request->validated(),
            $request->file('featured_image')
        );

        return redirect()->route('account.news.index')
            ->with('success', 'Your article was submitted for administrator review.');
    }
}
