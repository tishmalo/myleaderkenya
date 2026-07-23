<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\StoredPublicApprovalRepositoryInterface;
use App\Models\Candidate;
use App\Models\PublicApprovalScore;
use Illuminate\Support\Collection;

class StoredPublicApprovalRepository implements StoredPublicApprovalRepositoryInterface
{
    public function latestByCandidateIds(array $candidateIds): Collection
    {
        return PublicApprovalScore::query()
            ->whereIn('candidate_id', $candidateIds)
            ->get()
            ->keyBy('candidate_id');
    }

    public function upsertForCandidate(Candidate $candidate, string $profileSlug, float $approvalScore): PublicApprovalScore
    {
        return PublicApprovalScore::updateOrCreate(
            ['candidate_id' => $candidate->id],
            [
                'profile_slug' => $profileSlug,
                'approval_score' => $approvalScore,
                'source' => 'politiq',
                'fetched_at' => now(),
            ]
        );
    }
}