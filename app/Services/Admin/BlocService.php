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

    public function getPaginatedBlocs(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->blocRepository->paginate($perPage, $search);
    }

    public function getAllBlocs(): Collection
    {
        return $this->blocRepository->all();
    }

    public function getFormData(): array
    {
        return [
            'counties' => $this->blocRepository->allCounties(),
            'blocTypes' => $this->blocTypes(),
        ];
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

    private function transformData(array $data): array
    {
        $data['type'] = $data['type'] ?? 'economic';
        $data['county_ids'] = $data['county_ids'] ?? [];

        if (isset($data['tribes']) && is_string($data['tribes'])) {
            $data['tribes'] = collect(explode(',', $data['tribes']))
                ->map(fn ($tribe) => trim($tribe))
                ->filter()
                ->values()
                ->all();
        }

        if (isset($data['voting_patterns']) && is_string($data['voting_patterns'])) {
            $decoded = json_decode($data['voting_patterns'], true);
            $data['voting_patterns'] = is_array($decoded) ? $decoded : null;
        }

        return $data;
    }

    private function blocTypes(): array
    {
        return [
            'economic' => 'Economic',
            'political' => 'Political',
            'ethnic' => 'Ethnic',
        ];
    }
}