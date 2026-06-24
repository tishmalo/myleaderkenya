<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\DonorRepositoryInterface;
use App\Models\Donor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DonorService
{
    public function __construct(
        private DonorRepositoryInterface $donorRepository
    ) {}

    public function getDonorIndexData(int $perPage = 15): array
    {
        return [
            'donors'       => $this->donorRepository->paginate($perPage),
            'totalDonors'  => $this->donorRepository->count(),
            'totalAmount'  => $this->donorRepository->sumCompletedAmount(),
            'recentDonors' => $this->donorRepository->latestDonations(8),
        ];
    }

    public function createDonor(array $data): Donor
    {
        if (!isset($data['currency'])) {
            $data['currency'] = 'KES';
        }
        
        if (!isset($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        return $this->donorRepository->create($data);
    }

    public function updateDonor(Donor $donor, array $data): bool
    {
        if (!isset($data['currency'])) {
            $data['currency'] = 'KES';
        }

        return $this->donorRepository->update($donor, $data);
    }

    public function deleteDonor(Donor $donor): bool
    {
        return $this->donorRepository->delete($donor);
    }
}
