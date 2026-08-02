<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Web\CandidateRelationshipRepositoryInterface;
use App\Models\Candidate;
use App\Models\User;

class AspirantImpersonationService
{
    public function __construct(private CandidateRelationshipRepositoryInterface $relationships) {}

    public function canImpersonate(User $admin, User $target, Candidate $candidate): bool
    {
        if (! $admin->isAdmin() || $admin->is($target)) {
            return false;
        }

        return $this->relationships->userCanAccessCandidateDashboard($target, $candidate);
    }
}
