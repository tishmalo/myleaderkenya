<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\PoliticalPartyManagementRepositoryInterface;
use App\Models\PoliticalParty;
use App\Models\User;

class PoliticalPartyAccessService
{
    public function __construct(
        private PoliticalPartyManagementRepositoryInterface $parties,
    ) {}

    public function membership(User $user): ?PoliticalParty
    {
        return $this->parties->activePartyForUser($user);
    }

    public function authorize(
        User $user,
        PoliticalParty $party,
        bool $adminOnly = false,
    ): void {
        abort_unless($this->parties->userBelongsToParty($user, $party), 403);

        if ($adminOnly) {
            abort_unless($this->parties->userIsPartyAdmin($user, $party), 403);
        }
    }
}
