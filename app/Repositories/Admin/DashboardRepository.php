<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\DashboardRepositoryInterface;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\NewsArticle;
use App\Models\PoliticalParty;
use App\Models\Poll;
use App\Models\Position;
use App\Models\ResourceLink;
use App\Models\User;
use App\Models\Message;
use App\Models\PollingStation;
use App\Models\Bloc;
use App\Models\County;
use App\Models\Constituency;
use App\Models\Ward;
use App\Models\Tag;
use App\Models\Group;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getTotalUsersCount(): int
    {
        return User::count();
    }

    public function getTotalMessagesCount(): int
    {
        return Message::count();
    }

    public function getTotalGroupsCount(): int
    {
        return Group::count();
    }

    public function getLatestMessages(int $limit = 30)
    {
        return Message::latest()->take($limit)->get();
    }

    public function getLatestStations()
    {
        return PollingStation::latest()->get();
    }

    public function getTotalVotersCount(): int
    {
        return User::where('is_voter', true)->count();
    }

    public function getAverageVoterAge(): float
    {
        $avgYearOfBirth = User::whereNotNull('year_of_birth')
            ->where('year_of_birth', '>', 1900)
            ->avg('year_of_birth');

        if (!$avgYearOfBirth) {
            return 0;
        }

        return round(date('Y') - $avgYearOfBirth, 1);
    }

    public function getVotersCountByCounty()
    {
        return User::select('county')
            ->selectRaw('COUNT(*) as count')
            ->where('is_registered', true)
            ->whereNotNull('county')
            ->where('county', '!=', '')
            ->groupBy('county')
            ->orderByDesc('count')
            ->get();
    }

    public function getMessagesAndGroups($user): array
    {
        return [
            'messages' => Message::latest()->paginate(20),
            'groups'   => $user->groups()->withCount('members')->latest()->get()
        ];
    }

    public function getStationsAndBlocs(): array
    {
        return [
            'stations' => PollingStation::with('bloc')->latest()->get(),
            'blocs'    => Bloc::orderBy('name')->get()
        ];
    }

    public function createPollingStation(array $data)
    {
        return PollingStation::create($data);
    }

    public function updatePollingStation(PollingStation $station, array $data): PollingStation
    {
        $station->update($data);

        return $station;
    }

    public function deletePollingStation(PollingStation $station): void
    {
        $station->delete();
    }

    public function importStations(array $stations): int
    {
        $importedCount = 0;
        foreach ($stations as $station) {
            PollingStation::create([
                'county'             => $station['county'],
                'constituency'       => $station['constituency'],
                'office'             => $station['office'],
                'near_landmark'      => $station['near_landmark'] ?? null,
                'distance_to_office' => $station['distance_to_office'] ?? 0,
                'lat'                => $station['lat'],
                'lon'                => $station['lon'],
                'is_user_added'      => false,
            ]);
            $importedCount++;
        }
        return $importedCount;
    }

    public function getCountiesByBloc($blocId)
    {
        return County::whereHas('blocs', fn ($query) => $query->where('blocs.id', $blocId))->orderBy('name')->pluck('name');
    }

    public function getCountiesByName($name)
    {
        return County::where('name', $name)->orderBy('name')->pluck('name');
    }

    public function getConstituenciesByCounty($countyName)
    {
        return Constituency::where('county_id', function($query) use ($countyName) {
                    $query->select('id')->from('counties')->where('name', $countyName)->limit(1);
                })->orderBy('name')->pluck('name');
    }

    public function getWardsByConstituency($constituencyName)
    {
        return Ward::where('constituency_id', function($query) use ($constituencyName) {
                    $query->select('id')->from('constituencies')->where('name', $constituencyName)->limit(1);
                })->pluck('name')->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    public function getPollingStationsFiltered($type, $id)
    {
        $query = PollingStation::query();

        if ($type === 'county') {
            $query->where('county', function($q) use ($id) {
                $q->select('name')->from('counties')->where('id', $id);
            });
        } elseif ($type === 'constituency') {
            $query->where('constituency', function($q) use ($id) {
                $q->select('name')->from('constituencies')->where('id', $id);
            });
        } elseif ($type === 'ward') {
            $query->where('ward', function($q) use ($id) {
                $q->select('name')->from('wards')->where('id', $id);
            });
        }

        return $query->select('office', 'ward', 'registered_voters')->orderBy('office')->get();
    }

    public function getPollingStationsByWard($wardName)
    {
        return PollingStation::where('ward', $wardName)->orderBy('office')->pluck('office');
    }

    public function getTags()
    {
        return Tag::orderBy('name')->get();
    }

    /**
     * The aspirant and public content side of the dashboard: how many aspirants
     * per position, how many pages and links, parties, polls, stories and events.
     *
     * @return array<string, mixed>
     */
    public function getContentStats(): array
    {
        return [
            'aspirants'   => $this->aspirantStats(),
            'links'       => $this->linkStats(),
            'parties'     => [
                'total'     => PoliticalParty::count(),
                'published' => PoliticalParty::where('status', 'published')->count(),
            ],
            'polls'       => [
                'total'  => Poll::count(),
                'active' => Poll::where('status', Poll::STATUS_ACTIVE)->count(),
            ],
            'news'        => [
                'published' => NewsArticle::where('status', 'published')->count(),
                'draft'     => NewsArticle::where('status', 'draft')->count(),
            ],
            'events'      => [
                'total'   => Event::count(),
                'pending' => Event::where('approval_status', Event::STATUS_PENDING)->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function aspirantStats(): array
    {
        $rows = Candidate::query()
            ->select('position_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved")
            ->selectRaw("SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->groupBy('position_id')
            ->get();

        // Positions in the order the rest of the admin lists them.
        $positions = Position::query()->ordered()->pluck('name', 'id');
        $order = array_flip($positions->keys()->all());

        $byPosition = $rows
            ->filter(fn ($row): bool => $row->position_id !== null)
            ->map(fn ($row): array => [
                'position_id' => (int) $row->position_id,
                'label'       => $positions[$row->position_id] ?? 'Other',
                'total'       => (int) $row->total,
                'approved'    => (int) $row->approved,
                'pending'     => (int) $row->pending,
            ])
            ->sortBy(fn (array $row): int => $order[$row['position_id']] ?? PHP_INT_MAX)
            ->values();

        $unassigned = $rows->first(fn ($row): bool => $row->position_id === null);

        return [
            'total'      => (int) $rows->sum('total'),
            'approved'   => (int) $rows->sum('approved'),
            'pending'    => (int) $rows->sum('pending'),
            'byPosition' => $byPosition,
            'unassigned' => (int) ($unassigned->total ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function linkStats(): array
    {
        $counts = ResourceLink::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved")
            ->selectRaw("SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw('SUM(CASE WHEN county_id IS NULL THEN 1 ELSE 0 END) as national')
            ->first();

        $byCounty = ResourceLink::query()
            ->join('counties', 'counties.id', '=', 'resource_links.county_id')
            ->where('resource_links.approval_status', ResourceLink::STATUS_APPROVED)
            ->select('counties.name')
            ->selectRaw('COUNT(resource_links.id) as total')
            ->groupBy('counties.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'total'      => (int) ($counts->total ?? 0),
            'approved'   => (int) ($counts->approved ?? 0),
            'pending'    => (int) ($counts->pending ?? 0),
            'national'   => (int) ($counts->national ?? 0),
            'byCounty'   => $byCounty,
        ];
    }
}


