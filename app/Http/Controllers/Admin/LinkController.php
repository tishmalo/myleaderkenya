<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreUserLinkRequest;
use App\Models\ResourceLink;
use App\Services\Admin\LinkService;
use App\Services\Web\UserLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function __construct(private LinkService $links, private UserLinkService $userLinks) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['status']);

        return view('admin.links.index', [
            'links' => $this->links->paginate($filters),
            'statuses' => $this->links->statuses(),
            'activeStatus' => $filters['status'] ?? null,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.links.create', $this->userLinks->formData(
            array_values(array_filter((array) $request->old('candidate_id')))
        ));
    }

    public function store(StoreUserLinkRequest $request): RedirectResponse
    {
        $this->userLinks->submit($request->user(), $request->validated());

        return redirect()->route('links.index')
            ->with('success', 'Link submitted and awaiting approval.');
    }

    public function updateApproval(Request $request, ResourceLink $resourceLink): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        $this->links->review($resourceLink, $validated['status'], $request->user()->getKey());

        return redirect()->back()->with('success', $validated['status'] === 'approved'
            ? 'Link approved and published.'
            : 'Link rejected.');
    }

    public function destroy(ResourceLink $resourceLink)
    {
        $this->links->delete($resourceLink);

        return response()->json([
            'success' => true,
            'message' => 'Link deleted successfully.',
        ]);
    }
}
