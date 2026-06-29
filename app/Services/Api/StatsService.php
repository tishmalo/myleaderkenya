<?php

namespace App\Services\Api;

use App\Contracts\Repositories\Api\StatsRepositoryInterface;
use App\Contracts\Repositories\Admin\LiveStatFigureRepositoryInterface;

class StatsService
{
    public function __construct(
        private StatsRepositoryInterface $statsRepository,
        private LiveStatFigureRepositoryInterface $liveStatFigureRepository
    ) {}

    public function getTotalUsers(): array
    {
        return [
            'total' => $this->statsRepository->getTotalUsers()
        ];
    }

    public function getLiveStats(): array
    {
        $generatedFigures = $this->liveStatFigureRepository->activeTotals();
        $countyStats = $this->withGeneratedCountyDistribution(
            $this->statsRepository->getTopCountiesByUsers(10),
            $generatedFigures['confirmed_voters']
        );

        return [
            'confirmedVoters' => $this->statsRepository->getConfirmedVoters() + $generatedFigures['confirmed_voters'],
            'totalMessages' => $this->statsRepository->getTotalMessages() + $generatedFigures['total_messages'],
            'totalGroups' => $this->statsRepository->getTotalGroups(),
            'avgAge' => $this->statsRepository->getAverageAge(),
            'totalUsers' => $this->statsRepository->getTotalUsers() + $generatedFigures['total_users'],
            'stationsCount' => $this->statsRepository->getStationsCount() + $generatedFigures['stations_count'],
            'totalRegistered' => $this->statsRepository->getTotalRegistered() + $generatedFigures['confirmed_voters'],
            'maleRegistered' => $this->statsRepository->getMaleRegistered(),
            'femaleRegistered' => $this->statsRepository->getFemaleRegistered(),
            'countyLabels' => $countyStats['labels'],
            'countyData' => $countyStats['data'],
            'genderData' => $this->withGeneratedGenderDistribution([
                $this->statsRepository->getMaleCount(),
                $this->statsRepository->getFemaleCount(),
                $this->statsRepository->getOtherGenderCount(),
            ], $generatedFigures['confirmed_voters']),
        ];
    }
    private function withGeneratedCountyDistribution(array $countyStats, int $generatedVoters): array
    {
        if ($generatedVoters <= 0 || ! config('features.live_stats.demo_distribute_counties')) {
            return $countyStats;
        }

        if (empty($countyStats['labels']) || empty($countyStats['data'])) {
            return $countyStats;
        }

        $realTotal = max(array_sum(array_map('intval', $countyStats['data'])), 1);
        $remaining = $generatedVoters;
        $lastIndex = count($countyStats['data']) - 1;

        foreach ($countyStats['data'] as $index => $count) {
            $allocation = $index === $lastIndex
                ? $remaining
                : (int) floor($generatedVoters * ((int) $count / $realTotal));

            $allocation = min($allocation, $remaining);
            $countyStats['data'][$index] = (int) $count + $allocation;
            $remaining -= $allocation;
        }

        array_multisort($countyStats['data'], SORT_DESC, SORT_NUMERIC, $countyStats['labels']);

        return $countyStats;
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

