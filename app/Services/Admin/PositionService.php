<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\PositionRepositoryInterface;
use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PositionService
{
    public function __construct(
        private PositionRepositoryInterface $positionRepository
    ) {}

    public function getPaginatedPositions(int $perPage = 15): LengthAwarePaginator
    {
        return $this->positionRepository->paginate($perPage);
    }

    public function createPosition(array $data): Position
    {
        return $this->positionRepository->create($data);
    }

    public function updatePosition(Position $position, array $data): bool
    {
        return $this->positionRepository->update($position, $data);
    }

    public function deletePosition(Position $position): bool
    {
        return $this->positionRepository->delete($position);
    }
}
