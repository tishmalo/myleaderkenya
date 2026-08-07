<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\UserProfileRepositoryInterface;
use App\Models\Constituency;
use App\Models\County;
use App\Models\PollingStation;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Support\Collection;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function usernameExists(string $username, int $ignoreUserId): bool
    {
        return User::query()
            ->where('username', trim($username))
            ->where('id', '!=', $ignoreUserId)
            ->exists();
    }

    public function emailExists(string $email, int $ignoreUserId): bool
    {
        return User::query()
            ->where('email_hash', hash('sha256', strtolower(trim($email))))
            ->where('id', '!=', $ignoreUserId)
            ->exists();
    }

    public function counties(): Collection
    {
        return County::query()->orderBy('name')->pluck('name');
    }

    public function constituencies(string $county): Collection
    {
        return Constituency::query()
            ->whereHas('county', fn ($query) => $query->where('name', $county))
            ->orderBy('name')
            ->pluck('name');
    }

    public function wards(string $constituency): Collection
    {
        return Ward::query()
            ->whereHas('constituency', fn ($query) => $query->where('name', $constituency))
            ->orderBy('name')
            ->pluck('name');
    }

    public function pollingStations(string $ward): Collection
    {
        return PollingStation::query()
            ->where('ward', $ward)
            ->whereNotNull('office')
            ->where('office', '!=', '')
            ->orderBy('office')
            ->pluck('office')
            ->unique()
            ->values();
    }
}