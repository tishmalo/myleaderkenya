<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportWardRequest;
use App\Http\Requests\Admin\StoreWardRequest;
use App\Http\Requests\Admin\UpdateWardRequest;
use App\Models\Ward;
use App\Services\Admin\WardService;

class WardController extends Controller
{
    public function __construct(
        private WardService $wardService
    ) {}

    public function index()
    {
        $search = request('search');
        $wards = $this->wardService->getPaginatedWards(15, $search);
        return view('wards.index', compact('wards'));
    }

    public function create()
    {
        $constituencies = $this->wardService->getOrderedConstituency();
        return view('wards.create', compact('constituencies'));
    }

    public function store(StoreWardRequest $request)
    {
        $this->wardService->createWard($request->validated());

        return redirect()->route('wards.index')
            ->with('success', 'Ward created successfully');
    }

    public function edit(Ward $ward)
    {
        $constituencies = $this->wardService->getOrderedConstituency();
        return view('wards.edit', compact('ward', 'constituencies'));
    }

    public function update(UpdateWardRequest $request, Ward $ward)
    {
        $this->wardService->updateWard($ward, $request->validated());

        return redirect()->route('wards.index')
            ->with('success', 'Ward updated successfully');
    }

    public function destroy(Ward $ward)
    {
        $this->wardService->deleteWard($ward);

        return redirect()->route('wards.index')
            ->with('success', 'Ward deleted successfully');
    }

    public function import(ImportWardRequest $request)
    {
        $imported = $this->wardService->importWards($request->wards);

        return response()->json([
            'message' => 'Wards imported successfully',
            'imported' => $imported
        ]);
    }
}