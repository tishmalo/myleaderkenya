<?php

namespace App\Contracts\Repositories\Web;

use App\Models\Candidate;
use App\Models\CandidateClaimRequest;
use Illuminate\Database\Eloquent\Collection;

interface CandidateClaimRequestRepositoryInterface
{
    public function create(Candidate $candidate, array $data): CandidateClaimRequest;

    public function pendingDuplicate(Candidate $candidate, string $email, string $relationship): ?CandidateClaimRequest;

    public function forCandidate(Candidate $candidate): Collection;

    public function countsForCandidate(Candidate $candidate): array;

    public function markApproved(CandidateClaimRequest $claimRequest, int $reviewedBy, ?string $note = null): CandidateClaimRequest;

    public function markRejected(CandidateClaimRequest $claimRequest, int $reviewedBy, ?string $note = null): CandidateClaimRequest;
}
