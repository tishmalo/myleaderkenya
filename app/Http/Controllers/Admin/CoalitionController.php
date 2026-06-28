<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CoalitionStoreRequest;
use App\Http\Requests\Admin\CoalitionUpdateRequest;
use App\Models\Coalition;
use App\Services\Admin\CoalitionService;

class CoalitionController extends Controller
{
    public function __construct(private CoalitionService $coalitionService) {}

    public function index()
    {
        $filters = request()->only(['status', 'search']);
        $coalitions = $this->coalitionService->getPaginatedCoalitions($filters);

        return view('coalitions.index', compact('coalitions'));
    }

    public function create()
    {
        return view('coalitions.create', $this->coalitionService->getFormData());
    }

    public function store(CoalitionStoreRequest $request)
    {
        $this->coalitionService->createCoalition(
            $request->except(['logo', 'political_parties']),
            $request->file('logo'),
            $request->input('political_parties', [])
        );

        return redirect()->route('coalitions.index')
            ->with('success', 'Coalition created successfully!');
    }

    public function edit(Coalition $coalition)
    {
        $data = $this->coalitionService->getFormData();
        $data['coalition'] = $coalition->load('politicalParties');

        return view('coalitions.edit', $data);
    }

    public function update(CoalitionUpdateRequest $request, Coalition $coalition)
    {
        $this->coalitionService->updateCoalition(
            $coalition,
            $request->except(['logo', 'political_parties']),
            $request->file('logo'),
            $request->input('political_parties', [])
        );

        return redirect()->route('coalitions.index')
            ->with('success', 'Coalition updated successfully!');
    }

    public function destroy(Coalition $coalition)
    {
        $this->coalitionService->deleteCoalition($coalition);

        return response()->json([
            'success' => true,
            'message' => 'Coalition deleted successfully.',
        ]);
    }

    public function publicIndex()
    {
        $coalitions = $this->coalitionService->getPublishedCoalitions(12);

        return view('coalitions.public.index', compact('coalitions'));
    }

    public function publicShow(string $slug)
    {
        $coalition = $this->coalitionService->getPublicShowData($slug);

        return view('coalitions.public.show', compact('coalition'));
    }
}
