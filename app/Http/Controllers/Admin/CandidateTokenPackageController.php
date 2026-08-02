<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CandidateTokenPackageStoreRequest;
use App\Http\Requests\Admin\CandidateTokenPackageUpdateRequest;
use App\Models\CandidateTokenPackage;
use App\Services\Admin\CandidateTokenPackageService;

class CandidateTokenPackageController extends Controller
{
    public function __construct(private CandidateTokenPackageService $packages) {}

    public function index()
    {
        $packages = $this->packages->paginate(request()->only(['search', 'active']));

        return view('candidate-token-packages.index', compact('packages'));
    }

    public function create()
    {
        return view('candidate-token-packages.create');
    }

    public function store(CandidateTokenPackageStoreRequest $request)
    {
        $this->packages->create($request->validated());

        return redirect()->route('candidate-token-packages.index')->with('success', 'Token package created.');
    }

    public function edit(CandidateTokenPackage $candidateTokenPackage)
    {
        return view('candidate-token-packages.edit', compact('candidateTokenPackage'));
    }

    public function update(CandidateTokenPackageUpdateRequest $request, CandidateTokenPackage $candidateTokenPackage)
    {
        $this->packages->update($candidateTokenPackage, $request->validated());

        return redirect()->route('candidate-token-packages.index')->with('success', 'Token package updated.');
    }

    public function destroy(CandidateTokenPackage $candidateTokenPackage)
    {
        $this->packages->delete($candidateTokenPackage);

        return redirect()->route('candidate-token-packages.index')->with('success', 'Token package deleted.');
    }
}
