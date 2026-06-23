<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\BlocRepositoryInterface;
use App\Models\Bloc;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BlocService
{
    public function __construct(
        private BlocRepositoryInterface $blocRepository
    ) {}

    public function getPaginatedBlocs(int $perPage = 15): LengthAwarePaginator
    {
        return $this->blocRepository->paginate($perPage);
    }

    public function getAllBlocs(): Collection
    {
        return $this->blocRepository->all();
    }

    public function createBloc(array $data): Bloc
    {
        $data = $this->transformData($data);
        return $this->blocRepository->create($data);
    }

    public function updateBloc(Bloc $bloc, array $data): bool
    {
        $data = $this->transformData($data);
        return $this->blocRepository->update($bloc, $data);
    }

    public function deleteBloc(Bloc $bloc): bool
    {
        return $this->blocRepository->delete($bloc);
    }

    public function importBlocs(array $blocs): int
    {
        return $this->blocRepository->import($blocs);
    }

    /**
     * Transform request data into format suitable for storage.
     */
    private function transformData(array $data): array
    {
        // Convert tribes from comma-separated string to array
        if (isset($data['tribes']) && is_string($data['tribes'])) {
            $data['tribes'] = array_map('trim', explode(',', $data['tribes']));
        }

        // Convert voting_patterns from JSON string to array
        if (isset($data['voting_patterns']) && is_string($data['voting_patterns'])) {
            $decoded = json_decode($data['voting_patterns'], true);
            $data['voting_patterns'] = is_array($decoded) ? $decoded : null;
        }

        return $data;
    }
}
