<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\CandidateClaimRequestRepositoryInterface;
use App\Models\Candidate;
use App\Models\CandidateClaimRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CandidateClaimRequestRepository implements CandidateClaimRequestRepositoryInterface
{
    public function create(Candidate $candidate, array $data): CandidateClaimRequest
    {
        return $candidate->claimRequests()->create($data);
    }

    public function pendingDuplicate(Candidate $candidate, string $email, string $relationship): ?CandidateClaimRequest
    {
        return CandidateClaimRequest::query()
            ->where('candidate_id', $candidate->id)
            ->where('email_hash', hash('sha256', Str::lower(trim($email))))
            ->where('relationship', $relationship)
            ->where('status', CandidateClaimRequest::STATUS_PENDING)
            ->first();
    }

    public function forCandidate(Candidate $candidate): Collection
    {
        return $candidate->claimRequests()
            ->with(['user', 'reviewer'])
            ->latest()
            ->get();
    }

    public function countsForCandidate(Candidate $candidate): array
    {
        $counts = $candidate->claimRequests()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'pending' => (int) ($counts[CandidateClaimRequest::STATUS_PENDING] ?? 0),
            'approved' => (int) ($counts[CandidateClaimRequest::STATUS_APPROVED] ?? 0),
            'rejected' => (int) ($counts[CandidateClaimRequest::STATUS_REJECTED] ?? 0),
        ];
    }

    public function markApproved(CandidateClaimRequest $claimRequest, int $reviewedBy, ?string $note = null): CandidateClaimRequest
    {
        $claimRequest->forceFill([
            'status' => CandidateClaimRequest::STATUS_APPROVED,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'review_note' => $note,
        ])->save();

        return $claimRequest->refresh();
    }

    public function markRejected(CandidateClaimRequest $claimRequest, int $reviewedBy, ?string $note = null): CandidateClaimRequest
    {
        $claimRequest->forceFill([
            'status' => CandidateClaimRequest::STATUS_REJECTED,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'review_note' => $note,
        ])->save();

        return $claimRequest->refresh();
    }
}
