<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\CandidateRelationshipRepositoryInterface;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class CandidateRelationshipRepository implements CandidateRelationshipRepositoryInterface
{
    public function attach(User $user, Candidate $candidate, string $relationship): void
    {
        if (! Schema::hasTable('candidate_user_relationships')) {
            return;
        }

        $user->relatedCandidates()->syncWithoutDetaching([
            $candidate->id => array_filter([
                'relationship' => $relationship,
                'dashboard_access_enabled' => Schema::hasColumn('candidate_user_relationships', 'dashboard_access_enabled') ? true : null,
            ], fn ($value) => $value !== null),
        ]);
    }

    public function updateDashboardAccess(User $user, Candidate $candidate, bool $enabled): void
    {
        if (! Schema::hasTable('candidate_user_relationships') || ! Schema::hasColumn('candidate_user_relationships', 'dashboard_access_enabled')) {
            return;
        }

        $user->relatedCandidates()->updateExistingPivot($candidate->id, [
            'dashboard_access_enabled' => $enabled,
        ]);
    }

    public function detach(User $user, Candidate $candidate): void
    {
        if (! Schema::hasTable('candidate_user_relationships')) {
            return;
        }

        $user->relatedCandidates()->detach($candidate->id);
    }

    public function teamForCandidate(Candidate $candidate): Collection
    {
        if (! Schema::hasTable('candidate_user_relationships')) {
            return new Collection();
        }

        return $candidate->relatedUsers()
            ->whereIn('candidate_user_relationships.relationship', ['aspirant', 'PA', 'campaign_manager'])
            ->orderBy('candidate_user_relationships.created_at')
            ->get();
    }

    public function userCanAccessCandidateDashboard(User $user, Candidate $candidate): bool
    {
        if ((int) $candidate->user_id === (int) $user->id) {
            return true;
        }

        if (! Schema::hasTable('candidate_user_relationships')) {
            return false;
        }

        return $user->relatedCandidates()
            ->where('candidates.id', $candidate->id)
            ->whereIn('candidate_user_relationships.relationship', ['aspirant', 'PA', 'campaign_manager'])
            ->when(
                Schema::hasColumn('candidate_user_relationships', 'dashboard_access_enabled'),
                fn ($query) => $query->where('candidate_user_relationships.dashboard_access_enabled', true)
            )
            ->exists();
    }

    public function hasApprovedCandidateRelationship(User $user): bool
    {
        if (! Schema::hasTable('candidate_user_relationships')) {
            return false;
        }

        return $user->relatedCandidates()
            ->whereIn('candidate_user_relationships.relationship', ['aspirant', 'PA', 'campaign_manager'])
            ->when(
                Schema::hasColumn('candidate_user_relationships', 'dashboard_access_enabled'),
                fn ($query) => $query->where('candidate_user_relationships.dashboard_access_enabled', true)
            )
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
            ->when(
                Schema::hasColumn('candidate_user_relationships', 'dashboard_access_enabled'),
                fn ($query) => $query->where('candidate_user_relationships.dashboard_access_enabled', true)
            )
            ->latest('candidates.created_at')
            ->first();
    }
}
