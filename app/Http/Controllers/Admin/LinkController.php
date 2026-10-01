<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResourceLink;
use App\Services\Admin\LinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function __construct(private LinkService $links) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['status']);

        return view('admin.links.index', [
            'links' => $this->links->paginate($filters),
            'statuses' => $this->links->statuses(),
            'activeStatus' => $filters['status'] ?? null,
        ]);
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
