<?php

namespace App\Services\Api;

use App\Contracts\Repositories\Api\PollingStationRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Collection;

class PollingStationService
{
    public function __construct(
        private PollingStationRepositoryInterface $stationRepository
    ) {}

    public function addStation(array $data, ?User $user = null): array
    {
        $stationData = [
            'county'             => $data['county'],
            'constituency'       => $data['constituency'],
            'office'             => $data['office'],
            'near_landmark'      => $data['near_landmark'] ?? null,
            'distance_to_office' => $data['distance_to_office'] ?? 0,
            'lat'                => $data['lat'],
            'lon'                => $data['lon'],
            'is_user_added'      => $user ? true : false,
        ];

        $station = $this->stationRepository->create($stationData);

        return [
            'message' => 'Polling station added successfully',
            'station' => $station
        ];
    }

    public function getNearbyStations(float $lat, float $lon, ?int $radius = null): Collection
    {
        return $this->stationRepository->getNearby($lat, $lon, $radius ?? 10000);
    }

    public function getByCounty(string $county): Collection
    {
        return $this->stationRepository->getByCounty($county);
    }

    public function importStations(array $stations): array
    {
        $imported = $this->stationRepository->import($stations);

        return [
            'message'  => 'Import completed',
            'imported' => $imported
        ];
    }

    public function getAllStations(): Collection
    {
        return $this->stationRepository->all();
    }

    public function storeStation(array $data): void
    {
        $this->stationRepository->create($data);
    }

    public function getPollingStationsFiltered(string $type, string $id): Collection
    {
        return $this->stationRepository->getFiltered($type, $id);
    }
}
