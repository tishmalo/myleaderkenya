<?php

namespace App\Contracts\Repositories\Web;

use App\Models\Candidate;
use App\Models\PublicApprovalScore;
use Illuminate\Support\Collection;

interface StoredPublicApprovalRepositoryInterface
{
    public function latestByCandidateIds(array $candidateIds): Collection;

    public function upsertForCandidate(Candidate $candidate, string $profileSlug, float $approvalScore): PublicApprovalScore;
}