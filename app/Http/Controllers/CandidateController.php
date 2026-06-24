<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\CandidateStoreRequest;
use App\Http\Requests\Admin\CandidateUpdateRequest;
use App\Models\Candidate;
use App\Services\Admin\CandidateService;

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
        ['positions' => $positions, 'blocs' => $blocs] = $this->candidateService->getFormData();
        return view('candidates.create', compact('positions', 'blocs'));
    }

    public function store(CandidateStoreRequest $request)
    {
        $this->candidateService->createCandidate(
            $request->except('profile_picture'),
            $request->file('profile_picture')
        );

        return redirect()->route('candidates.index')
                         ->with('success', 'Candidate added successfully!');
    }

    public function edit(Candidate $candidate)
    {
        ['positions' => $positions, 'blocs' => $blocs] = $this->candidateService->getFormData();
        return view('candidates.edit', compact('candidate', 'positions', 'blocs'));
    }

    public function update(CandidateUpdateRequest $request, Candidate $candidate)
    {
        $this->candidateService->updateCandidate(
            $candidate,
            $request->except('profile_picture'),
            $request->file('profile_picture')
        );

        return redirect()->route('candidates.index')
                         ->with('success', 'Candidate updated successfully!');
    }

    public function destroy(Candidate $candidate)
    {
        $this->candidateService->deleteCandidate($candidate);

        return response()->json([
            'success' => true,
            'message' => 'Candidate deleted successfully.',
        ]);
    }

    public function publicIndex()
    {
        $filters = request()->only(['search', 'county', 'position']);

        ['candidates' => $candidates, 'positions' => $positions, 'counties' => $counties]
            = $this->candidateService->getPublicIndex($filters);

        return view('aspirants.public.index', compact('candidates', 'positions', 'counties'));
    }

    public function publicShow(Candidate $candidate)
    {
        $candidate    = $this->candidateService->getPublicShow($candidate);
        $relatedArticles = $candidate->relatedArticles;

        return view('aspirants.public.show', compact('candidate', 'relatedArticles'));
    }
}
