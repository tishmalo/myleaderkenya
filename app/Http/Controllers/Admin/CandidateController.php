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
        $filters = request()->only(['candidate', 'position', 'political_party', 'approval_status']);
        $candidates = $this->candidateService->getPaginatedCandidates(15, $filters);
        $formData = $this->candidateService->getFormData();

        return view('candidates.index', array_merge($formData, compact('candidates')));
    }


    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $candidates = Candidate::query()
            ->select(['id', 'name', 'nick_name'])
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('nick_name', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn (Candidate $candidate) => [
                'id' => $candidate->id,
                'text' => trim($candidate->name . ($candidate->nick_name ? ' (' . $candidate->nick_name . ')' : '')),
            ]);

        return response()->json(['results' => $candidates]);
    }
    public function create()
    {
        return view('candidates.create', $this->candidateService->getFormData());
    }

    public function store(CandidateStoreRequest $request)
    {
        $this->candidateService->createCandidate(
            $request->validated(),
            $request->file('profile_picture')
        );

        return redirect()->route('candidates.index')
                         ->with('success', 'Aspirant added successfully.');
    }

    public function edit(Candidate $candidate)
    {
        return view('candidates.edit', array_merge($this->candidateService->getFormData(), compact('candidate')));
    }

    public function update(CandidateUpdateRequest $request, Candidate $candidate)
    {
        $this->candidateService->updateCandidate(
            $candidate,
            $request->validated(),
            $request->file('profile_picture')
        );

        return redirect()->route('candidates.index')
                         ->with('success', 'Aspirant updated successfully.');
    }

    public function toggleFeatured(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'featured' => ['required', 'boolean'],
        ]);

        $candidate->update(['featured' => $data['featured']]);

        return response()->json([
            'success' => true,
            'featured' => $candidate->featured,
        ]);
    }

    public function updateApproval(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'approval_status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $candidate->update(['approval_status' => $data['approval_status']]);

        return response()->json([
            'success' => true,
            'approval_status' => $candidate->approval_status,
        ]);
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
        $data = $this->candidateService->getPublicIndex($request->only(['candidate', 'search', 'position', 'political_party', 'country', 'county', 'constituency', 'ward', 'bloc']), 12);
        return view('aspirants.public.index', $data);
    }

    public function publicShow(Candidate $candidate)
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('candidates', 'approval_status') && $candidate->approval_status !== 'approved') {
            abort(404);
        }

        $candidate = $this->candidateService->getPublicShow($candidate);
        return view('aspirants.public.show', compact('candidate'));
    }
}
