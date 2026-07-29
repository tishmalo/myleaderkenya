<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\CandidateRelationshipRepositoryInterface;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class CandidateRelationshipRepository implements CandidateRelationshipRepositoryInterface
{
    public function attach(User $user, Candidate $candidate, string $relationship): void
    {
        if (! Schema::hasTable('candidate_user_relationships')) {
            return;
        }

        $user->relatedCandidates()->syncWithoutDetaching([
            $candidate->id => ['relationship' => $relationship],
        ]);
    }

    public function hasApprovedCandidateRelationship(User $user): bool
    {
        if (! Schema::hasTable('candidate_user_relationships')) {
            return false;
        }

        return $user->relatedCandidates()
            ->whereIn('candidate_user_relationships.relationship', ['aspirant', 'PA', 'campaign_manager'])
            ->exists();
    }

    public function firstRelatedCandidate(User $user): ?Candidate
    {
        if (! Schema::hasTable('candidate_user_relationships')) {
            return null;
        }

        return $user->relatedCandidates()
            ->with(['position', 'politicalParty'])
            ->whereIn('candidate_user_relationships.relationship', ['aspirant', 'PA', 'campaign_manager'])
            ->latest('candidates.created_at')
            ->first();
    }
}
