<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\DashboardRepositoryInterface;
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
        return County::where('bloc_id', $blocId)->orderBy('name')->pluck('name');
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
                })->orderBy('name')->pluck('name');
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
}
