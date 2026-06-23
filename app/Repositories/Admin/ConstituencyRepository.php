<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\ConstituencyRepositoryInterface;
use App\Models\Constituency;
use App\Models\County;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ConstituencyRepository implements ConstituencyRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = Constituency::with('county.bloc')
            ->withCount('pollingStations')
            ->withSum('pollingStations', 'registered_voters');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function getOrderedCounties(): Collection {
        return County::orderBy('name')->get();
    }

    public function create(array $data): Constituency
    {
        return Constituency::create($data);
    }

    public function update(Constituency $constituency, array $data): bool
    {
        return $constituency->update($data);
    }

    public function delete(Constituency $constituency): bool
    {
        return $constituency->delete();
    }

    public function import(array $constituencies): int
    {
        $imported = 0;

        foreach ($constituencies as $data) {
            Constituency::updateOrCreate(
                ['name' => $data['name'], 'county_id' => $data['county_id']],
                [
                    'population' => $data['population'] ?? null,
                    'number_of_seats' => $data['number_of_seats'] ?? 1,
                    'registered_voters' => $data['registered_voters'] ?? null,
                    'position_name' => $data['position_name'] ?? null,
                ]
            );
            $imported++;
        }

        return $imported;
    }

    public function all(): Collection
    {
        return Constituency::with('county')->orderBy('name')->get();
    }
}
