<?php

namespace App\Repositories\Api;

use App\Contracts\Repositories\Api\LocationRepositoryInterface;
use App\Models\County;
use App\Models\Constituency;
use App\Models\Location;
use App\Models\Ward;
use App\Models\PollingStation;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Collection;

class LocationRepository implements LocationRepositoryInterface
{
    public function getAllCounties(): Collection
    {
        return County::orderBy('name')->pluck('name');
    }

    public function getCountiesByBloc(int $blocId): Collection
    {
        return County::whereHas('blocs', fn ($query) => $query->where('blocs.id', $blocId))
            ->orderBy('name')
            ->pluck('name');
    }

    public function getCountiesByName(string $name): Collection
    {
        return County::where('name', $name)
            ->orderBy('name')
            ->pluck('name');
    }

    public function getConstituenciesByCountyName(string $countyName): Collection
    {
        return Constituency::join('counties', 'constituencies.county_id', '=', 'counties.id')
            ->where('counties.name', $countyName)
            ->orderBy('constituencies.name')
            ->pluck('constituencies.name');
    }

    public function getWardsByConstituencyName(string $constituencyName): Collection
    {
        return Ward::join('constituencies', 'wards.constituency_id', '=', 'constituencies.id')
            ->where('constituencies.name', $constituencyName)
            ->pluck('wards.name')
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public function getPollingStationsByType(string $type, int $id): Collection
    {
        if ($type === 'county') {
            return PollingStation::join('counties', 'polling_stations.county', '=', 'counties.name')
                ->where('counties.id', $id)
                ->select('polling_stations.office', 'polling_stations.ward', 'polling_stations.registered_voters')
                ->orderBy('polling_stations.office')
                ->get();
        } elseif ($type === 'constituency') {
            return PollingStation::join('constituencies', 'polling_stations.constituency', '=', 'constituencies.name')
                ->where('constituencies.id', $id)
                ->select('polling_stations.office', 'polling_stations.ward', 'polling_stations.registered_voters')
                ->orderBy('polling_stations.office')
                ->get();
        } elseif ($type === 'ward') {
            return PollingStation::join('wards', 'polling_stations.ward', '=', 'wards.name')
                ->where('wards.id', $id)
                ->select('polling_stations.office', 'polling_stations.ward', 'polling_stations.registered_voters')
                ->orderBy('polling_stations.office')
                ->get();
        }

        return collect([]);
    }

    public function getPollingStationsByWardName(string $wardName): Collection
    {
        return PollingStation::where('ward', $wardName)
            ->orderBy('office')
            ->pluck('office');
    }

    public function getTags(): Collection
    {
        return Tag::orderBy('name')->get();
    }

    public function getAllBlocs(): Collection
    {
        return \App\Models\Bloc::orderBy('name')->get();
    }

    public function getAllLocations(): Collection
    {
        return Location::select('name', 'latitude', 'longitude')->get();
    }

    public function uploadLocation(User $user, float $latitude, float $longitude): void
    {
        Location::updateOrCreate(
            ['name' => $user->username],
            ['latitude' => $latitude, 'longitude' => $longitude]
        );
    }
}

