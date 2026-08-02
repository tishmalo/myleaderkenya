<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\CandidateCampaignPriority;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CandidateCampaignPriorityReviewController extends Controller
{
    public function update(Request $request, Candidate $candidate, CandidateCampaignPriority $candidateCampaignPriority): RedirectResponse
    {
        if ((int) $candidateCampaignPriority->candidate_id !== (int) $candidate->id) {
            abort(404);
        }

        $data = $request->validate(['status' => ['required', 'in:approved,rejected']]);
        $candidateCampaignPriority->update([
            'status' => $data['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Campaign priority '.($data['status'] === 'approved' ? 'approved.' : 'rejected.'));
    }
}