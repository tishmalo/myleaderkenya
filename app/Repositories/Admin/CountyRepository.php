<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CountyRepositoryInterface;
use App\Models\County;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CountyRepository implements CountyRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = County::with('bloc')
            ->withCount('pollingStations')
            ->withSum('pollingStations', 'registered_voters');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): County
    {
        return County::create($data);
    }

    public function update(County $county, array $data): County
    {
        $county->update($data);
        return $county;
    }

    public function delete(County $county): bool
    {
        return $county->delete();
    }


    public function getOrderedBlocs(): Collection{
        return County::with('bloc')->orderBy('name')->get()->pluck('bloc')->unique('id')->values();
    }

    public function import(array $counties): int
    {
        $imported = 0;

        foreach ($counties as $countyData) {
            County::updateOrCreate(
                ['name' => $countyData['name']],
                [
                    'bloc_id' => $countyData['bloc_id'],
                    'area' => $countyData['area'] ?? null,
                    'population' => $countyData['population'] ?? null,
                    'capital' => $countyData['capital'] ?? null,
                    'registered_voters' => $countyData['registered_voters'] ?? null,
                    'postal_abbreviation' => $countyData['postal_abbreviation'] ?? null,
                ]
            );
            $imported++;
        }

        return $imported;
    }

    public function all(): Collection
    {
        return County::with('bloc')->orderBy('name')->get();
    }
}
