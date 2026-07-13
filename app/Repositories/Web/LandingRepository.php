<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\LandingRepositoryInterface;
use App\Models\Candidate;
use App\Models\Message;
use App\Models\NewsArticle;
use App\Models\LiveStatFigure;
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
        $hasDob             = Schema::hasColumn('users', 'dob');
        $hasCounty          = Schema::hasColumn('users', 'county');
        $hasGender          = Schema::hasColumn('users', 'gender');
        $hasFeatured        = Schema::hasColumn('candidates', 'featured');
        $generatedFigures   = $this->activeGeneratedFigures();

        $voterStats = [
            'confirmedVoters' => $this->confirmedVotersCount() + $generatedFigures['confirmed_voters'],

            'avgAge' => $hasDob
                ? round(User::whereNotNull('dob')->avg(DB::raw('TIMESTAMPDIFF(YEAR, dob, CURDATE())')))
                : null,

            'byCounty' => $hasCounty
                ? User::select('county', DB::raw('count(*) as count'))
                    ->whereNotNull('county')
                    ->groupBy('county')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get()
                : collect(),
        ];

        $countyCollection = $this->withGeneratedCountyDistribution($voterStats['byCounty'], $generatedFigures['confirmed_voters']);
        $voterStats['byCounty'] = $countyCollection;

        return [
            'totalUsers'     => User::count() + $generatedFigures['total_users'],
            'totalMessages'  => (class_exists(\App\Models\Message::class) ? Message::count() : 0) + $generatedFigures['total_messages'],
            'stationsCount'  => (class_exists(\App\Models\Station::class) ? Station::count() : 0) + $generatedFigures['stations_count'],
            'voterStats'     => $voterStats,
            'countyLabels'   => $countyCollection->pluck('county'),
            'countyData'     => $countyCollection->pluck('count'),
            'genderData'     => $hasGender
                ? $this->withGeneratedGenderDistribution([
                    User::where('gender', 'male')->count(),
                    User::where('gender', 'female')->count(),
                    User::whereNotIn('gender', ['male', 'female'])->orWhereNull('gender')->count(),
                ], $generatedFigures['confirmed_voters'])
                : [0, 0, 0],
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
            ['label' => 'Presidential', 'position' => 'presidential', 'aliases' => ['presidential', 'president'], 'county_scoped' => false],
            ['label' => 'Governor', 'position' => 'governor', 'aliases' => ['governor'], 'county_scoped' => true],
            ['label' => 'Senator', 'position' => 'senator', 'aliases' => ['senator'], 'county_scoped' => true],
            ['label' => 'Women Rep', 'position' => 'women-rep', 'aliases' => ['women rep', 'woman rep', 'women representative', 'woman representative'], 'county_scoped' => true],
            ['label' => 'MP', 'position' => 'mp', 'aliases' => ['mp', 'member of parliament'], 'county_scoped' => true],
            ['label' => 'MCA', 'position' => 'mca', 'aliases' => ['mca', 'member of county assembly'], 'county_scoped' => true],
        ];

        return collect($groups)->map(function (array $group) use ($hasFeatured) {
            $query = Candidate::query()
                ->with(['position', 'politicalParty'])
                ->when(Schema::hasColumn('candidates', 'approval_status'), fn ($query) => $query->where('approval_status', 'approved'))
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

            if ($group['county_scoped']) {
                $query->orderByRaw("CASE WHEN county IS NULL OR county = '' THEN 1 ELSE 0 END")
                    ->orderBy('county');
            }

            $group['candidates'] = $query->latest()->take(10)->get();

            return $group;
        })->all();
    }
    private function confirmedVotersCount(): int
    {
        if (Schema::hasColumn('users', 'is_voter')) {
            return User::where('is_voter', true)->count();
        }

        if (Schema::hasColumn('users', 'is_registered')) {
            return User::where('is_registered', true)->count();
        }

        if (Schema::hasColumn('users', 'voter_registered')) {
            return User::where('voter_registered', true)->count();
        }

        return User::count();
    }
    private function activeGeneratedFigures(): array
    {
        $defaults = array_fill_keys(array_keys(LiveStatFigure::METRICS), 0);

        if (! Schema::hasTable('live_stat_figures')) {
            return $defaults;
        }

        return array_merge($defaults, LiveStatFigure::query()
            ->where('active', true)
            ->select('metric_key', DB::raw('SUM(value) as total'))
            ->groupBy('metric_key')
            ->pluck('total', 'metric_key')
            ->map(fn ($value) => (int) $value)
            ->all());
    }
    private function withGeneratedCountyDistribution($counties, int $generatedVoters)
    {
        if ($generatedVoters <= 0 || ! config('features.live_stats.demo_distribute_counties')) {
            return $counties;
        }

        if ($counties->isEmpty()) {
            return $counties;
        }

        $realTotal = max((int) $counties->sum('count'), 1);
        $remaining = $generatedVoters;
        $lastIndex = $counties->count() - 1;

        return $counties
            ->values()
            ->map(function ($county, int $index) use (&$remaining, $generatedVoters, $realTotal, $lastIndex) {
                $allocation = $index === $lastIndex
                    ? $remaining
                    : (int) floor($generatedVoters * ((int) $county->count / $realTotal));

                $allocation = min($allocation, $remaining);
                $county->count = (int) $county->count + $allocation;
                $remaining -= $allocation;

                return $county;
            })
            ->sortByDesc('count')
            ->values();
    }
    private function withGeneratedGenderDistribution(array $genderData, int $generatedVoters): array
    {
        if ($generatedVoters <= 0 || ! config('features.live_stats.demo_distribute_counties')) {
            return $genderData;
        }

        $realTotal = array_sum(array_map('intval', $genderData));

        if ($realTotal <= 0) {
            $male = (int) floor($generatedVoters * 0.55);
            $female = (int) floor($generatedVoters * 0.44);

            return [$male, $female, $generatedVoters - $male - $female];
        }

        $remaining = $generatedVoters;
        $lastIndex = count($genderData) - 1;

        foreach ($genderData as $index => $count) {
            $allocation = $index === $lastIndex
                ? $remaining
                : (int) floor($generatedVoters * ((int) $count / $realTotal));

            $allocation = min($allocation, $remaining);
            $genderData[$index] = (int) $count + $allocation;
            $remaining -= $allocation;
        }

        return $genderData;
    }
}
