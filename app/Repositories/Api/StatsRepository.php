<?php

namespace App\Repositories\Api;

use App\Contracts\Repositories\Api\StatsRepositoryInterface;
use App\Models\User;
use App\Models\Message;
use App\Models\PollingStation;
use App\Models\Group;

class StatsRepository implements StatsRepositoryInterface
{
    public function getTotalUsers(): int
    {
        return User::count();
    }

    public function getConfirmedVoters(): int
    {
        return User::where('is_registered', true)->count();
    }

    public function getTotalMessages(): int
    {
        return Message::count();
    }

    public function getStationsCount(): int
    {
        return PollingStation::count();
    }

    public function getTotalGroups(): int
    {
        return Group::count();
    }

    public function getAverageAge(): ?float
    {
        $avgYearOfBirth = User::whereNotNull('year_of_birth')
            ->avg('year_of_birth');

        if (!$avgYearOfBirth) {
            return null;
        }

        return round(date('Y') - $avgYearOfBirth);
    }

    public function getTotalRegistered(): int
    {
        return User::where('is_registered', true)->count();
    }

    public function getMaleRegistered(): int
    {
        return User::where('gender', 'male')
            ->where('is_registered', true)
            ->count();
    }

    public function getFemaleRegistered(): int
    {
        return User::where('gender', 'female')
            ->where('is_registered', true)
            ->count();
    }

    public function getMaleCount(): int
    {
        return User::where('gender', 'male')->count();
    }

    public function getFemaleCount(): int
    {
        return User::where('gender', 'female')->count();
    }

    public function getOtherGenderCount(): int
    {
        return User::whereNotIn('gender', ['male', 'female'])
            ->orWhereNull('gender')
            ->count();
    }

    public function getTopCountiesByUsers(int $limit = 10): array
    {
        $results = User::query()
            ->whereNotNull('county')
            ->selectRaw('county, count(*) as total')
            ->groupBy('county')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return [
            'labels' => $results->pluck('county')->toArray(),
            'data' => $results->pluck('total')->toArray(),
        ];
    }
}
