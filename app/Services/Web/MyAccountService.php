<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\CandidateRelationshipRepositoryInterface;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class MyAccountService
{
    public function __construct(
        private CandidateClaimRequestService $claims,
        private CandidateRelationshipRepositoryInterface $relationships,
    ) {}

    public function dashboardData(User $user): array
    {
        return [
            'claims' => $this->claims->claimsForUser($user),
            'candidates' => $this->relationships->accessibleCandidates($user),
        ];
    }

    public function selectCandidate(User $user, int $candidateId): Candidate
    {
        $candidate = $this->relationships->findAccessibleCandidate($user, $candidateId);
        if (! $candidate) {
            throw ValidationException::withMessages(['candidate_id' => 'You do not have dashboard access to that aspirant.']);
        }

        session(['active_candidate_id' => $candidate->id]);
        return $candidate;
    }
}
