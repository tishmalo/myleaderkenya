<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\LandingRepositoryInterface;
use App\Models\User;
use App\Models\Message;
use App\Models\Station;
use App\Models\NewsArticle;
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
        $hasVoterRegistered = Schema::hasColumn('users', 'voter_registered');
        $hasDob             = Schema::hasColumn('users', 'dob');
        $hasCounty          = Schema::hasColumn('users', 'county');
        $hasGender          = Schema::hasColumn('users', 'gender');

        $voterStats = [
            'confirmedVoters' => $hasVoterRegistered
                ? User::where('voter_registered', true)->count()
                : User::count(), // fallback: total users

            'avgAge' => $hasDob
                ? round(User::whereNotNull('dob')->avg(DB::raw('TIMESTAMPDIFF(YEAR, dob, CURDATE())')))
                : null,

            'byCounty' => $hasCounty
                ? User::select('county', DB::raw('count(*) as count'))
                    ->whereNotNull('county')
                    ->groupBy('county')
                    ->orderByDesc('count')
                    ->get()
                : collect(),
        ];

        $countyCollection = $voterStats['byCounty'];

        return [
            'totalUsers'     => User::count(),
            'totalMessages'  => class_exists(\App\Models\Message::class) ? Message::count() : 0,
            'stationsCount'  => class_exists(\App\Models\Station::class) ? Station::count() : 0,
            'voterStats'     => $voterStats,
            'countyLabels'   => $countyCollection->pluck('county'),
            'countyData'     => $countyCollection->pluck('count'),
            'genderData'     => $hasGender ? [
                User::where('gender', 'male')->count(),
                User::where('gender', 'female')->count(),
                User::whereNotIn('gender', ['male', 'female'])->orWhereNull('gender')->count(),
            ] : [0, 0, 0],
            'latestBlogs'    => NewsArticle::query()
                ->where('status', 'published')
                ->latest('published_at')
                ->latest()
                ->take(3)
                ->get(),
        ];
    }
}

