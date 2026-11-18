<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewCampaignPriorityRequest;
use App\Models\Candidate;
use App\Models\CandidateCampaignPriority;
use Illuminate\Http\RedirectResponse;

class CandidateCampaignPriorityReviewController extends Controller
{
    public function update(ReviewCampaignPriorityRequest $request, Candidate $candidate, CandidateCampaignPriority $candidateCampaignPriority): RedirectResponse
    {
        if ((int) $candidateCampaignPriority->candidate_id !== (int) $candidate->id) {
            abort(404);
        }

        $status = $request->status();

        $candidateCampaignPriority->update([
            'status' => $status,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Campaign priority '.($status === 'approved' ? 'approved.' : 'rejected.'));
    }
}