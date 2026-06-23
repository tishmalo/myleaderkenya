<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\WardRepositoryInterface;
use App\Models\Ward;
use App\Models\Constituency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WardRepository implements WardRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = Ward::with('constituency.county')
            ->withCount('pollingStations')
            ->withSum('pollingStations', 'registered_voters');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Ward
    {
        return Ward::create($data);
    }

    public function update(Ward $ward, array $data): bool
    {
        return $ward->update($data);
    }

    public function getOrderedConstituency():collection
    {
        return Constituency::with('county')
            ->get()
            ->sortBy('name')
            ->values();
    }


    public function delete(Ward $ward): bool
    {
        return $ward->delete();
    }

    public function import(array $wards): int
    {
        $imported = 0;

        foreach ($wards as $data) {
            Ward::updateOrCreate(
                ['name' => $data['name'], 'constituency_id' => $data['constituency_id']],
                [
                    'population' => $data['population'] ?? null,
                    'registered_voters' => $data['registered_voters'] ?? null,
                ]
            );
            $imported++;
        }

        return $imported;
    }

    public function all(): Collection
    {
        return Ward::with('constituency')->orderBy('name')->get();
    }
}
