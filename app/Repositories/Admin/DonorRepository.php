<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\DonorRepositoryInterface;
use App\Models\Donor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DonorRepository implements DonorRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Donor::latest()->paginate($perPage);
    }

    public function create(array $data): Donor
    {
        return Donor::create($data);
    }

    public function update(Donor $donor, array $data): bool
    {
        return $donor->update($data);
    }

    public function delete(Donor $donor): bool
    {
        return $donor->delete();
    }

    public function count(): int
    {
        return Donor::count();
    }

    public function sumCompletedAmount(): float
    {
        return (float) Donor::where('status', 'completed')->sum('amount');
    }

    public function latestDonations(int $limit = 8): Collection
    {
        return Donor::latest()->take($limit)->get();
    }
}
