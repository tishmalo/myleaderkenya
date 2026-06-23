<?php

namespace App\Services\Api;

use App\Contracts\Repositories\Api\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Collection;

class VoterService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function updateVoterStatus(User $user, array $data): array
    {
        $this->userRepository->update($user, $data);

        return [
            'message' => 'Voter status updated successfully',
            'status'  => $user->only(['is_voter', 'county', 'age', 'gender'])
        ];
    }

    public function getVoterStatus(User $user): array
    {
        return [
            'is_voter' => $user->is_voter,
            'county'   => $user->county,
            'age'      => $user->age,
        ];
    }

    public function getAllVoters(): Collection
    {
        return $this->userRepository->getAllVoters();
    }

    public function getVoterStats(): array
    {
        return $this->userRepository->getVoterStats();
    }
}
