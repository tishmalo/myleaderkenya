<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PoliticalPartyStoreRequest;
use App\Http\Requests\Admin\PoliticalPartyUpdateRequest;
use App\Models\PoliticalParty;
use App\Services\Admin\PoliticalPartyService;

class PoliticalPartyController extends Controller
{
    public function __construct(private PoliticalPartyService $politicalPartyService) {}

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

    public function destroy(PoliticalParty $politicalParty)
    {
        $this->politicalPartyService->deleteParty($politicalParty);

        return response()->json([
            'success' => true,
            'message' => 'Political party deleted successfully.',
        ]);
    }

    public function publicIndex()
    {
        $politicalParties = $this->politicalPartyService->getPublishedParties(12);

        return view('political-parties.public.index', compact('politicalParties'));
    }

    public function publicShow(string $slug)
    {
        $politicalParty = $this->politicalPartyService->getPublicShowData($slug);

        return view('political-parties.public.show', compact('politicalParty'));
    }
}
