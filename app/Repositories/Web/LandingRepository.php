<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\LandingRepositoryInterface;
use App\Models\Candidate;
use App\Models\Message;
use App\Models\NewsArticle;
use App\Models\Station;
use App\Models\User;
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
        $hasFeatured        = Schema::hasColumn('candidates', 'featured');

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
            'homepageAspirantGroups' => $this->homepageAspirantGroups($hasFeatured),
        ];
    }

    private function homepageAspirantGroups(bool $hasFeatured): array
    {
        $groups = [
            ['label' => 'Presidential', 'position' => 'presidential', 'aliases' => ['presidential', 'president']],
            ['label' => 'Governor', 'position' => 'governor', 'aliases' => ['governor']],
            ['label' => 'Senator', 'position' => 'senator', 'aliases' => ['senator']],
            ['label' => 'Women Rep', 'position' => 'women-rep', 'aliases' => ['women rep', 'woman rep', 'women representative', 'woman representative']],
            ['label' => 'MP', 'position' => 'mp', 'aliases' => ['mp', 'member of parliament']],
            ['label' => 'MCA', 'position' => 'mca', 'aliases' => ['mca', 'member of county assembly']],
        ];

        return collect($groups)->map(function (array $group) use ($hasFeatured) {
            $query = Candidate::query()
                ->with(['position', 'politicalParty'])
                ->whereHas('position', function ($positionQuery) use ($group) {
                    $positionQuery->where(function ($query) use ($group) {
                        foreach ($group['aliases'] as $alias) {
                            $query->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($alias) . '%']);
                        }
                    });
                });

            if ($hasFeatured) {
                $query->orderByDesc('featured');
            }

            $group['candidates'] = $query->latest()->take(10)->get();

            return $group;
        })->all();
    }
}
