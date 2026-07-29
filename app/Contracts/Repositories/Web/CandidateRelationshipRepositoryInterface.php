<?php

namespace App\Contracts\Repositories\Web;

use App\Models\Candidate;
use App\Models\User;

interface CandidateRelationshipRepositoryInterface
{
    public function attach(User $user, Candidate $candidate, string $relationship): void;

    public function hasApprovedCandidateRelationship(User $user): bool;

    public function firstRelatedCandidate(User $user): ?Candidate;
}
