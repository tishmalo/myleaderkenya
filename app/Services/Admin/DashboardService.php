<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\DashboardRepositoryInterface;

class DashboardService
{
    public function __construct(
        private DashboardRepositoryInterface $dashboardRepository
    ) {}

    public function getDashboardStats(): array
    {
        return [
            'totalUsers'   => $this->dashboardRepository->getTotalUsersCount(),
            'totalMessages'=> $this->dashboardRepository->getTotalMessagesCount(),
            'totalGroups'  => $this->dashboardRepository->getTotalGroupsCount(),
            'messages'     => $this->dashboardRepository->getLatestMessages(30),
            'stations'     => $this->dashboardRepository->getLatestStations(),
            'totalVoters'  => $this->dashboardRepository->getTotalVotersCount(),
            'voterStats'   => [
                'confirmedVoters' => $this->dashboardRepository->getTotalVotersCount(),
                'avgAge'          => $this->dashboardRepository->getAverageVoterAge(),
                'byCounty'        => $this->dashboardRepository->getVotersCountByCounty(),
            ],
        ];
    }

    public function getVoterStats(): array
    {
        return [
            'totalVoters' => $this->dashboardRepository->getTotalVotersCount(),
            'voterStats'  => [
                'confirmedVoters' => $this->dashboardRepository->getTotalVotersCount(),
                'avgAge'          => $this->dashboardRepository->getAverageVoterAge(),
                'byCounty'        => $this->dashboardRepository->getVotersCountByCounty(),
            ]
        ];
    }

    public function getMessagesAndGroups($user): array
    {
        return $this->dashboardRepository->getMessagesAndGroups($user);
    }

    public function getStationsAndBlocs(): array
    {
        return $this->dashboardRepository->getStationsAndBlocs();
    }

    public function createPollingStation(array $data)
    {
        return $this->dashboardRepository->createPollingStation([
            'bloc_id'            => $data['bloc_id'] ?? null,
            'county'             => $data['county'],
            'constituency'       => $data['constituency'],
            'ward'               => $data['ward'],
            'office'             => $data['office'],
            'near_landmark'      => $data['near_landmark'] ?? null,
            'distance_to_office' => $data['distance_to_office'] ?? 0,
            'lat'                => $data['lat'],
            'lon'                => $data['lon'],
            'registered_voters'  => $data['registered_voters'] ?? 0,
            'is_user_added'      => true,
        ]);
    }

    public function importStations(array $stations): int
    {
        $importedCount = 0;
        foreach ($stations as $station) {
            $this->dashboardRepository->createPollingStation([
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
        return $this->dashboardRepository->getCountiesByBloc($blocId);
    }

    public function getCountiesByName($name)
    {
        return $this->dashboardRepository->getCountiesByName($name);
    }

    public function getConstituenciesByCounty($countyName)
    {
        return $this->dashboardRepository->getConstituenciesByCounty($countyName);
    }

    public function getWardsByConstituency($constituencyName)
    {
        return $this->dashboardRepository->getWardsByConstituency($constituencyName);
    }

    public function getPollingStationsFiltered($type, $id)
    {
        return $this->dashboardRepository->getPollingStationsFiltered($type, $id);
    }

    public function getPollingStationsByWard($wardName)
    {
        return $this->dashboardRepository->getPollingStationsByWard($wardName);
    }

    public function getTags()
    {
        return $this->dashboardRepository->getTags();
    }
}
