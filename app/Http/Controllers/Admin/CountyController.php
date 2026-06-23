<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportCountyRequest;
use App\Http\Requests\Admin\StoreCountyRequest;
use App\Http\Requests\Admin\UpdateCountyRequest;
use App\Models\County;
use App\Models\Bloc;
use App\Services\Admin\CountyService;

class CountyController extends Controller
{
    public function __construct(
        private CountyService $countyService
    ) {}

    public function index()
    {
        $search = request('search');
        $counties = $this->countyService->getPaginatedCounties(15, $search);
        return view('counties.index', compact('counties'));
    }

    public function create()
    {
        $blocs = $this->countyService->getOrderedBlocs();
        return view('counties.create', compact('blocs'));
    }

    public function store(StoreCountyRequest $request)
    {
        $this->countyService->createCounty($request->validated());

        return redirect()->route('counties.index')
            ->with('success', 'County created successfully');
    }

    public function edit(County $county)
    {
        $blocs = $this->countyService->getOrderedBlocs();
        return view('counties.edit', compact('county', 'blocs'));
    }

    public function update(UpdateCountyRequest $request, County $county)
    {
        $this->countyService->updateCounty($county, $request->validated());

        return redirect()->route('counties.index')
            ->with('success', 'County updated successfully');
    }

    public function destroy(County $county)
    {
        $this->countyService->deleteCounty($county);

        return redirect()->route('counties.index')
            ->with('success', 'County deleted successfully');
    }

    public function import(ImportCountyRequest $request)
    {
        $imported = $this->countyService->importCounties($request->counties);

        return response()->json([
            'message' => 'Counties imported successfully',
            'imported' => $imported
        ]);
    }
}