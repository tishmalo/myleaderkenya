<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CountyRepositoryInterface;
use App\Models\Bloc;
use App\Models\County;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CountyRepository implements CountyRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = County::with(['bloc', 'blocs'])
            ->withCount('pollingStations')
            ->withSum('pollingStations', 'registered_voters');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): County
    {
        $blocIds = $this->extractBlocIds($data);
        $data['bloc_id'] = $blocIds[0] ?? ($data['bloc_id'] ?? null);
        unset($data['bloc_ids']);

        $county = County::create($data);
        $this->syncBlocs($county, $blocIds);

        return $county->fresh(['bloc', 'blocs']);
    }

    public function update(County $county, array $data): County
    {
        $blocIds = $this->extractBlocIds($data);
        $data['bloc_id'] = $blocIds[0] ?? ($data['bloc_id'] ?? null);
        unset($data['bloc_ids']);

        $previousBlocIds = $county->blocs()->pluck('blocs.id')->all();
        $county->update($data);
        $this->syncBlocs($county, $blocIds, $previousBlocIds);

        return $county->fresh(['bloc', 'blocs']);
    }

    public function delete(County $county): bool
    {
        $blocIds = $county->blocs()->pluck('blocs.id')->all();
        $county->blocs()->detach();
        $deleted = $county->delete();
        $this->recalculateTotals($blocIds);

        return $deleted;
    }

    public function getOrderedBlocs(): Collection
    {
        return Bloc::orderBy('name')->get();
    }

    public function import(array $counties): int
    {
        $imported = 0;

        foreach ($counties as $countyData) {
            $blocIds = $countyData['bloc_ids'] ?? [];
            if (empty($blocIds) && ! empty($countyData['bloc_id'])) {
                $blocIds = [$countyData['bloc_id']];
            }

            $county = County::updateOrCreate(
                ['name' => $countyData['name']],
                [
                    'bloc_id' => $blocIds[0] ?? null,
                    'area' => $countyData['area'] ?? null,
                    'population' => $countyData['population'] ?? null,
                    'capital' => $countyData['capital'] ?? null,
                    'registered_voters' => $countyData['registered_voters'] ?? null,
                    'postal_abbreviation' => $countyData['postal_abbreviation'] ?? null,
                ]
            );

            $this->syncBlocs($county, $blocIds);
            $imported++;
        }

        return $imported;
    }

    public function all(): Collection
    {
        return County::with(['bloc', 'blocs'])->orderBy('name')->get();
    }

    private function extractBlocIds(array $data): array
    {
        $blocIds = $data['bloc_ids'] ?? [];

        if (empty($blocIds) && ! empty($data['bloc_id'])) {
            $blocIds = [$data['bloc_id']];
        }

        return collect($blocIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function syncBlocs(County $county, array $blocIds, ?array $previousBlocIds = null): void
    {
        if (! Schema::hasTable('bloc_county')) {
            return;
        }

        $previousBlocIds ??= $county->blocs()->pluck('blocs.id')->all();
        $county->blocs()->sync($blocIds);

        $this->recalculateTotals(array_unique(array_merge($previousBlocIds, $blocIds)));
    }

    private function recalculateTotals(array $blocIds): void
    {
        if (! Schema::hasTable('bloc_county')) {
            return;
        }

        collect($blocIds)->filter()->unique()->each(function ($blocId) {
            $totals = County::query()
                ->join('bloc_county', 'counties.id', '=', 'bloc_county.county_id')
                ->where('bloc_county.bloc_id', $blocId)
                ->selectRaw('COALESCE(SUM(counties.population), 0) as population')
                ->selectRaw('COALESCE(SUM(counties.registered_voters), 0) as registered_voters')
                ->first();

            Bloc::whereKey($blocId)->update([
                'total_population' => (int) $totals->population,
                'total_registered_voters' => (int) $totals->registered_voters,
            ]);
        });
    }
}