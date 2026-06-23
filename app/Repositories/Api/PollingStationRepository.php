<?php

namespace App\Repositories\Api;

use App\Contracts\Repositories\Api\PollingStationRepositoryInterface;
use App\Models\PollingStation;
use Illuminate\Support\Collection;

class PollingStationRepository implements PollingStationRepositoryInterface
{
    public function create(array $data): PollingStation
    {
        $data['is_user_added'] = $data['is_user_added'] ?? false;
        $data['distance_to_office'] = $data['distance_to_office'] ?? 0;
        $data['registered_voters'] = $data['registered_voters'] ?? 0;

        return PollingStation::create($data);
    }

    public function getNearby(float $lat, float $lon, int $radius = 10000): Collection
    {
        return PollingStation::nearby($lat, $lon, $radius)->get();
    }

    public function getByCounty(string $county): Collection
    {
        return PollingStation::where('county', 'LIKE', "%{$county}%")
            ->orderBy('constituency')
            ->orderBy('office')
            ->get();
    }

    public function import(array $stations): int
    {
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
        }

        return count($stations);
    }

    public function all(): Collection
    {
        return PollingStation::all();
    }

    public function latest(): Collection
    {
        return PollingStation::with('bloc')->latest()->get();
    }

    public function getFiltered(string $type, string $id): Collection
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

        return $query->select('office', 'ward', 'registered_voters')
                     ->orderBy('office')
                     ->get();
    }
}
