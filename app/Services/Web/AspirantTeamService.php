<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\CandidateRelationshipRepositoryInterface;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AspirantTeamService
{
    public function __construct(private CandidateRelationshipRepositoryInterface $relationships) {}

    public function teamForOwner(User $owner, ?Candidate $candidate): Collection
    {
        if (! $candidate || ! $this->isPrimaryAspirant($owner, $candidate)) {
            return new Collection();
        }

        return $this->relationships->teamForCandidate($candidate)
            ->reject(fn (User $member): bool => $member->id === $owner->id)
            ->values();
    }

    public function removeMember(User $owner, Candidate $candidate, User $member): bool
    {
        if (! $this->isPrimaryAspirant($owner, $candidate) || $member->id === $owner->id) {
            return false;
        }

        $this->relationships->detach($member, $candidate);

        return true;
    }

    public function isPrimaryAspirant(User $user, ?Candidate $candidate): bool
    {
        return $candidate !== null && (int) $candidate->user_id === (int) $user->id;
    }
}
