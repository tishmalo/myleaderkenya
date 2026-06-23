<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\LandingRepositoryInterface;
use App\Models\User;
use App\Models\Message;
use App\Models\Station;
use App\Models\Group;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LandingRepository implements LandingRepositoryInterface
{
    /**
     * Get statistics for the landing page.
     *
     * @return array
     */
    public function getLandingStats(): array
    {
        // Check which columns actually exist before querying
        $hasIsRegistered    = Schema::hasColumn('users', 'is_registered');
        $hasYearOfBirth     = Schema::hasColumn('users', 'year_of_birth');
        $hasCounty          = Schema::hasColumn('users', 'county');
        $hasGender          = Schema::hasColumn('users', 'gender');

        $registeredVoters = $hasIsRegistered
            ? User::where('is_voter', true)->count()
            : 0;

        // Calculate average age from year_of_birth
        $avgAge = null;
        if ($hasYearOfBirth) {
            $avgYearOfBirth = User::whereNotNull('year_of_birth')
                ->where('year_of_birth', '>', 1900)
                ->avg('year_of_birth');

            if ($avgYearOfBirth) {
                $avgAge = date('Y') - round($avgYearOfBirth);
            }
        }

        $voterStats = [
            'confirmedVoters' => $registeredVoters,
            'avgAge' => $avgAge,

            'byCounty' => $hasCounty
                ? User::select('county', DB::raw('count(*) as count'))
                    ->whereNotNull('county')
                    ->where('county', '!=', '')
                    ->groupBy('county')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get()
                : collect(),
        ];

        $countyCollection = $voterStats['byCounty'];

        return [
            'totalUsers'     => User::count(),
            'totalMessages'  => class_exists(Message::class) ? Message::count() : 0,
            'stationsCount'  => class_exists(Station::class) ? Station::count() : 0,
            'totalGroups'    => class_exists(Group::class) ? Group::count() : 0,
            'voterStats'     => $voterStats,
            'countyLabels'   => $countyCollection->pluck('county'),
            'countyData'     => $countyCollection->pluck('count'),
            'genderData'     => $hasGender ? [
                User::where('gender', 'male')->count(),
                User::where('gender', 'female')->count(),
                User::whereNotIn('gender', ['male', 'female'])->orWhereNull('gender')->count(),
            ] : [0, 0, 0],
        ];
    }
}
