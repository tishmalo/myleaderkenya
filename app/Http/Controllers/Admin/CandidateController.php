<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CandidateStoreRequest;
use App\Http\Requests\Admin\CandidateUpdateRequest;
use App\Models\Candidate;
use App\Services\Admin\CandidateService;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function __construct(
        private CandidateService $candidateService
    ) {}

    public function index()
    {
        $candidates = $this->candidateService->getPaginatedCandidates();
        return view('candidates.index', compact('candidates'));
    }

    public function create()
    {
        
        return view('candidates.create');
    }

    public function store(CandidateStoreRequest $request)
    {
        $this->candidateService->createCandidate(
            $request->validated(),
            $request->file('image')
        );

        return redirect()->route('candidates.index')
                         ->with('success', 'Aspirant added successfully.');
    }

    public function edit(Candidate $candidate)
    {
        return view('candidates.edit', compact('candidate'));
    }

    public function update(CandidateUpdateRequest $request, Candidate $candidate)
    {
        $this->candidateService->updateCandidate(
            $candidate,
            $request->validated(),
            $request->file('image')
        );

        return redirect()->route('candidates.index')
                         ->with('success', 'Aspirant updated successfully.');
    }

    public function destroy(Candidate $candidate)
    {
        $this->candidateService->deleteCandidate($candidate);

        return response()->json([
            'success' => true,
            'message' => 'Aspirant deleted successfully.'
        ]);
    }

    public function publicIndex(Request $request)
    {
        $data = $this->candidateService->getPublicIndex($request->only(['search', 'county', 'position']), 12);
        return view('aspirants.public.index', $data);
    }

    public function publicShow(Candidate $candidate)
    {
        $candidate = $this->candidateService->getPublicShow($candidate);
        return view('aspirants.public.show', compact('candidate'));
    }
}
