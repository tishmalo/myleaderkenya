<?php

namespace App\Services\Api;

use App\Contracts\Repositories\Api\StatsRepositoryInterface;

class StatsService
{
    public function __construct(
        private StatsRepositoryInterface $statsRepository
    ) {}

    public function getTotalUsers(): array
    {
        return [
            'total' => $this->statsRepository->getTotalUsers()
        ];
    }

    public function getLiveStats(): array
    {
        $countyStats = $this->statsRepository->getTopCountiesByUsers(10);

        return [
            'confirmedVoters' => $this->statsRepository->getConfirmedVoters(),
            'totalMessages' => $this->statsRepository->getTotalMessages(),
            'totalGroups' => $this->statsRepository->getTotalGroups(),
            'avgAge' => $this->statsRepository->getAverageAge(),
            'totalUsers' => $this->statsRepository->getTotalUsers(),
            'totalRegistered' => $this->statsRepository->getTotalRegistered(),
            'maleRegistered' => $this->statsRepository->getMaleRegistered(),
            'femaleRegistered' => $this->statsRepository->getFemaleRegistered(),
            'countyLabels' => $countyStats['labels'],
            'countyData' => $countyStats['data'],
            'genderData' => [
                $this->statsRepository->getMaleCount(),
                $this->statsRepository->getFemaleCount(),
                $this->statsRepository->getOtherGenderCount(),
            ],
        ];
    }
}
