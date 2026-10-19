<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PoliticalPartyStoreRequest;
use App\Http\Requests\Admin\PoliticalPartyUpdateRequest;
use App\Models\PoliticalParty;
use App\Services\Admin\PoliticalPartyService;
use App\Services\Web\ViewTracker;
use Illuminate\Http\Request;

class PoliticalPartyController extends Controller
{
    public function __construct(
        private PoliticalPartyService $politicalPartyService,
        private ViewTracker $viewTracker,
    ) {}

    public function index()
    {
        $filters = request()->only(['status', 'search']);
        $politicalParties = $this->politicalPartyService->getPaginatedParties($filters);

        return view('political-parties.index', compact('politicalParties'));
    }

    public function create()
    {
        return view('political-parties.create');
    }

    public function store(PoliticalPartyStoreRequest $request)
    {
        $this->politicalPartyService->createParty(
            $request->except(['logo']),
            $request->file('logo')
        );

        return redirect()->route('political-parties.index')
            ->with('success', 'Political party created successfully!');
    }

    public function edit(PoliticalParty $politicalParty)
    {
        return view('political-parties.edit', compact('politicalParty'));
    }

    public function update(PoliticalPartyUpdateRequest $request, PoliticalParty $politicalParty)
    {
        $this->politicalPartyService->updateParty(
            $politicalParty,
            $request->except(['logo']),
            $request->file('logo')
        );

        return redirect()->route('political-parties.index')
            ->with('success', 'Political party updated successfully!');
    }

    public function destroy(Request $request, PoliticalParty $politicalParty)
    {
        $this->politicalPartyService->deleteParty($politicalParty);

        $message = 'Political party deleted successfully.';

        if (! $request->expectsJson()) {
            return redirect()->back()->with('success', $message);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function publicIndex()
    {
        $politicalParties = $this->politicalPartyService->getPublishedParties(12);

        return view('political-parties.public.index', compact('politicalParties'));
    }

    public function publicShow(string $slug)
    {
        $data = $this->politicalPartyService->getPublicPageData($slug, 20);

        $this->viewTracker->track($data['politicalParty'], request());

        return view('political-parties.public.show', $data);
    }
}
