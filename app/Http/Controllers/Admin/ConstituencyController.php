<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportConstituencyRequest;
use App\Http\Requests\Admin\StoreConstituencyRequest;
use App\Http\Requests\Admin\UpdateConstituencyRequest;
use App\Models\Constituency;
use App\Models\County;
use App\Services\Admin\ConstituencyService;

class ConstituencyController extends Controller
{
    public function __construct(
        private ConstituencyService $constituencyService
    ) {}

    public function index()
    {
        $search = request('search');
        $constituencies = $this->constituencyService->getPaginatedConstituencies(15, $search);
        return view('constituencies.index', compact('constituencies'));
    }

    public function create()
    {
        $counties = $this->constituencyService->getOrderedCounties();
        return view('constituencies.create', compact('counties'));
    }

    public function store(StoreConstituencyRequest $request)
    {
        $this->constituencyService->createConstituency($request->validated());

        return redirect()->route('constituencies.index')
            ->with('success', 'Constituency created successfully');
    }

    public function edit(Constituency $constituency)
    {
        $counties = $this->constituencyService->getOrderedCounties();
        return view('constituencies.edit', compact('constituency', 'counties'));
    }

    public function update(UpdateConstituencyRequest $request, Constituency $constituency)
    {
        $this->constituencyService->updateConstituency($constituency, $request->validated());

        return redirect()->route('constituencies.index')
            ->with('success', 'Constituency updated successfully');
    }

    public function destroy(Constituency $constituency)
    {
        $this->constituencyService->deleteConstituency($constituency);

        return redirect()->route('constituencies.index')
            ->with('success', 'Constituency deleted successfully');
    }

    public function import(ImportConstituencyRequest $request)
    {
        $imported = $this->constituencyService->importConstituencies($request->constituencies);

        return response()->json([
            'message' => 'Constituencies imported successfully',
            'imported' => $imported
        ]);
    }
}