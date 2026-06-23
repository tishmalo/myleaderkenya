<?php

namespace App\Services\Api;

use App\Contracts\Repositories\Api\LocationRepositoryInterface;
use App\Contracts\Repositories\Api\MessageRepositoryInterface;
use App\Contracts\Repositories\Api\PollingStationRepositoryInterface;
use App\Contracts\Repositories\Api\UserRepositoryInterface;
use App\Models\User;

class DashboardService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MessageRepositoryInterface $messageRepository,
        private PollingStationRepositoryInterface $stationRepository,
        private LocationRepositoryInterface $locationRepository
    ) {}

    public function getDashboardData(): array
    {
        $voterStats = $this->userRepository->getVoterStats();

        return [
            'totalUsers'    => $this->userRepository->count(),
            'totalMessages' => $this->messageRepository->count(),
            'messages'      => $this->messageRepository->latest(30),
            'stations'      => $this->stationRepository->latest(),
            'totalVoters'   => $this->userRepository->countVoters(),
            'voterStats'    => $voterStats,
        ];
    }

    public function getMessages(User $user): array
    {
        $messages = $this->messageRepository->latest(50);

        $groups = $user->groups()
            ->withCount('members')
            ->latest()
            ->get();

        return compact('messages', 'groups');
    }

    public function getStats(): array
    {
        $totalVoters = $this->userRepository->countVoters();
        $voterStats = $this->userRepository->getVoterStats();

        return compact('totalVoters', 'voterStats');
    }

    public function getStations(): array
    {
        $stations = $this->stationRepository->latest();
        $blocs = $this->locationRepository->getAllBlocs();

        return compact('stations', 'blocs');
    }
}
