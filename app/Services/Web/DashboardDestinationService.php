<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\PoliticalPartyManagementRepositoryInterface;
use App\Models\User;

class DashboardDestinationService
{
    public function __construct(private PoliticalPartyManagementRepositoryInterface $parties) {}

    public function urlFor(User $user, bool $absolute = true): string
    {
        if ($user->isAdmin()) {
            return route('dashboard', absolute: $absolute);
        }

        if ($this->parties->activePartyForUser($user)) {
            return route('party.dashboard', absolute: $absolute);
        }

        return route('my-account', absolute: $absolute);
    }
}
