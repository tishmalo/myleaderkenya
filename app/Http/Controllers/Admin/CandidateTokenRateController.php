<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CandidateTokenRateStoreRequest;
use App\Http\Requests\Admin\CandidateTokenRateUpdateRequest;
use App\Models\CandidateTokenRate;
use App\Services\Admin\CandidateTokenRateService;

class CandidateTokenRateController extends Controller
{
    public function __construct(private CandidateTokenRateService $rates) {}

    public function index()
    {
        $rates = $this->rates->paginate(request()->only(['search', 'active']));

        return view('candidate-token-rates.index', compact('rates'));
    }

    public function create()
    {
        return view('candidate-token-rates.create');
    }

    public function store(CandidateTokenRateStoreRequest $request)
    {
        $this->rates->create($request->validated());

        return redirect()->route('candidate-token-rates.index')->with('success', 'Token rate created.');
    }

    public function edit(CandidateTokenRate $candidateTokenRate)
    {
        return view('candidate-token-rates.edit', compact('candidateTokenRate'));
    }

    public function update(CandidateTokenRateUpdateRequest $request, CandidateTokenRate $candidateTokenRate)
    {
        $this->rates->update($candidateTokenRate, $request->validated());

        return redirect()->route('candidate-token-rates.index')->with('success', 'Token rate updated.');
    }

    public function destroy(CandidateTokenRate $candidateTokenRate)
    {
        $this->rates->delete($candidateTokenRate);

        return redirect()->route('candidate-token-rates.index')->with('success', 'Token rate deleted.');
    }
}
