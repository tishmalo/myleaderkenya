<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DonorStoreRequest;
use App\Http\Requests\Admin\DonorUpdateRequest;
use App\Models\Donor;
use App\Services\Admin\DonorService;

class DonorController extends Controller
{
    public function __construct(
        private DonorService $donorService
    ) {}

    public function index()
    {
        return view('donors.index', $this->donorService->getDonorIndexData());
    }

    public function create()
    {
        return view('donors.create');
    }

    public function store(DonorStoreRequest $request)
    {
        $this->donorService->createDonor($request->validated());

        return redirect()->route('donors.index')
                         ->with('success', 'Donor record created successfully.');
    }

    public function edit(Donor $donor)
    {
        return view('donors.edit', compact('donor'));
    }

    public function update(DonorUpdateRequest $request, Donor $donor)
    {
        $this->donorService->updateDonor($donor, $request->validated());

        return redirect()->route('donors.index')
                         ->with('success', 'Donor record updated successfully.');
    }

    public function destroy(Donor $donor)
    {
        $this->donorService->deleteDonor($donor);

        return redirect()->route('donors.index')
                         ->with('success', 'Donor record deleted successfully.');
    }
}
