<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreUserLinkRequest;
use App\Services\Web\UserLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserLinkController extends Controller
{
    public function __construct(private UserLinkService $links) {}

    public function index(Request $request): View
    {
        return view('account.links.index', [
            'links' => $this->links->listFor($request->user()),
        ]);
    }

    public function create(Request $request): View
    {
        return view('account.links.create', $this->links->formData(
            array_values(array_filter((array) $request->old('candidate_id')))
        ));
    }

    public function store(StoreUserLinkRequest $request): RedirectResponse
    {
        $this->links->submit($request->user(), $request->validated());

        return redirect()->route('account.links.index')
            ->with('success', 'Your link was submitted and is awaiting administrator approval.');
    }
}
