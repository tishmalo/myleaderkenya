<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\DashboardRepositoryInterface;
use App\Contracts\Repositories\Admin\LiveStatFigureRepositoryInterface;

class DashboardService
{
    public function __construct(
        private DashboardRepositoryInterface $dashboardRepository,
        private LiveStatFigureRepositoryInterface $liveStatFigureRepository
    ) {}

    public function getDashboardStats(): array
    {
        $generatedFigures = $this->liveStatFigureRepository->activeTotals();
        $totalUsers = $this->dashboardRepository->getTotalUsersCount() + $generatedFigures['total_users'];
        $totalMessages = $this->dashboardRepository->getTotalMessagesCount() + $generatedFigures['total_messages'];
        $totalVoters = $this->dashboardRepository->getTotalVotersCount() + $generatedFigures['confirmed_voters'];
        $votersByCounty = $this->withGeneratedCountyDistribution(
            $this->dashboardRepository->getVotersCountByCounty(),
            $generatedFigures['confirmed_voters']
        );

        return [
            'totalUsers'   => $totalUsers,
            'totalMessages'=> $totalMessages,
            'totalGroups'  => $this->dashboardRepository->getTotalGroupsCount(),
            'messages'     => $this->dashboardRepository->getLatestMessages(30),
            'stations'     => $this->dashboardRepository->getLatestStations(),
            'totalVoters'  => $totalVoters,
            'voterStats'   => [
                'confirmedVoters' => $totalVoters,
                'avgAge'          => $this->dashboardRepository->getAverageVoterAge(),
                'byCounty'        => $votersByCounty,
            ],
        ];
    }

    public function getVoterStats(): array
    {
        $generatedFigures = $this->liveStatFigureRepository->activeTotals();
        $totalRegisteredVoters = $this->dashboardRepository->getTotalUsersCount() + $generatedFigures['total_users'];
        $confirmedVoters = $this->dashboardRepository->getTotalVotersCount() + $generatedFigures['confirmed_voters'];
        $votersByCounty = $this->withGeneratedCountyDistribution(
            $this->dashboardRepository->getVotersCountByCounty(),
            $generatedFigures['confirmed_voters']
        );

        return [
            'totalVoters' => $totalRegisteredVoters,
            'totalRegisteredVoters' => $totalRegisteredVoters,
            'voterStats'  => [
                'confirmedVoters' => $confirmedVoters,
                'avgAge'          => $this->dashboardRepository->getAverageVoterAge(),
                'byCounty'        => $votersByCounty,
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
        return $this->dashboardRepository->importStations($stations);
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
}



