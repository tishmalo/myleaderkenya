<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\PoliticalPartyManagementRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class DashboardDestinationService
{
    public function __construct(
        private PoliticalPartyManagementRepositoryInterface $parties,
        private UserProfileService $profiles,
    ) {}

    public function urlFor(User $user, bool $absolute = true): string
    {
        if ($user->isAdmin()) {
            return $this->adminDestination($user, $absolute);
        }

        if (! $this->profiles->isComplete($user)) {
            return route('account.profile.edit', absolute: $absolute);
        }

        if ($this->parties->activePartyForUser($user)) {
            return route('party.dashboard', absolute: $absolute);
        }

        return route('my-account', absolute: $absolute);
    }

    /**
     * The dashboard itself needs dashboard.view, which a narrow staff role may
     * not hold. Those accounts land on the first admin page their role can
     * open instead of hitting a 403 straight after signing in.
     */
    private function adminDestination(User $user, bool $absolute): string
    {
        if ($user->canAccess('dashboard.view')) {
            return route('dashboard', absolute: $absolute);
        }

        $route = $this->firstAccessibleAdminRoute($user);

        return $route
            ? route($route, absolute: $absolute)
            : route('my-account', absolute: $absolute);
    }

    private function firstAccessibleAdminRoute(User $user): ?string
    {
        foreach (config('backend.sidebar.sections', []) as $section) {
            foreach ($section['items'] ?? [] as $item) {
                $route = $item['route'] ?? null;
                $permission = $item['permission'] ?? null;

                if (! $route || ! $permission || ! Route::has($route)) {
                    continue;
                }

                if (($item['superadmin_only'] ?? false) && ! $user->isSuperAdmin()) {
                    continue;
                }

                if ($user->canAccess($permission)) {
                    return $route;
                }
            }
        }

        return null;
    }
}
