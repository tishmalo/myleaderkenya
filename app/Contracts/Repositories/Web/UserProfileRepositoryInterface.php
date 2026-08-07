<?php

namespace App\Contracts\Repositories\Web;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserProfileRepositoryInterface
{
    public function update(User $user, array $data): bool;

    public function usernameExists(string $username, int $ignoreUserId): bool;

    public function emailExists(string $email, int $ignoreUserId): bool;

    public function counties(): Collection;

    public function constituencies(string $county): Collection;

    public function wards(string $constituency): Collection;

    public function pollingStations(string $ward): Collection;
}