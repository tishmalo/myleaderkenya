<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\BlocRepositoryInterface;
use App\Models\Bloc;
use App\Models\County;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class BlocRepository implements BlocRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = Bloc::with('counties')->withCount('counties');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Bloc
    {
        $countyIds = $data['county_ids'] ?? [];
        unset($data['county_ids']);

        $bloc = Bloc::create($data);
        $this->syncCounties($bloc, $countyIds);

        return $bloc->fresh('counties');
    }

    public function update(Bloc $bloc, array $data): bool
    {
        $countyIds = $data['county_ids'] ?? [];
        unset($data['county_ids']);

        $updated = $bloc->update($data);
        $this->syncCounties($bloc, $countyIds);

        return $updated;
    }

    public function delete(Bloc $bloc): bool
    {
        $bloc->counties()->detach();

        County::where('bloc_id', $bloc->id)->update(['bloc_id' => null]);

        return $bloc->delete();
    }

    public function import(array $blocs): int
    {
        $imported = 0;

        foreach ($blocs as $blocData) {
            $countyIds = $blocData['county_ids'] ?? [];
            unset($blocData['county_ids']);

            $bloc = Bloc::updateOrCreate(
                ['name' => $blocData['name']],
                [
                    'type' => $blocData['type'] ?? 'economic',
                    'description' => $blocData['description'] ?? null,
                    'tribes' => $blocData['tribes'] ?? null,
                    'tribe_population' => $blocData['tribe_population'] ?? null,
                    'voting_patterns' => $blocData['voting_patterns'] ?? null,
                ]
            );

            if (! empty($countyIds)) {
                $this->syncCounties($bloc, $countyIds);
            } else {
                $this->recalculateTotals($bloc);
            }

            $imported++;
        }

        return $imported;
    }

    public function all(): Collection
    {
        return Bloc::orderBy('name')->get();
    }

    public function allCounties(): Collection
    {
        return County::orderBy('name')->get();
    }

    private function syncCounties(Bloc $bloc, array $countyIds): void
    {
        $countyIds = collect($countyIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (! Schema::hasTable('bloc_county')) {
            return;
        }

        $previousCountyIds = $bloc->counties()->pluck('counties.id')->all();
        $bloc->counties()->sync($countyIds);

        County::whereIn('id', $countyIds)
            ->where(function ($query) use ($bloc) {
                $query->whereNull('bloc_id')->orWhere('bloc_id', $bloc->id);
            })
            ->update(['bloc_id' => $bloc->id]);

        County::whereIn('id', array_diff($previousCountyIds, $countyIds))
            ->where('bloc_id', $bloc->id)
            ->update(['bloc_id' => null]);

        $this->recalculateTotals($bloc);
    }

    private function recalculateTotals(Bloc $bloc): void
    {
        if (! Schema::hasTable('bloc_county')) {
            return;
        }

        $totals = County::query()
            ->join('bloc_county', 'counties.id', '=', 'bloc_county.county_id')
            ->where('bloc_county.bloc_id', $bloc->id)
            ->selectRaw('COALESCE(SUM(counties.population), 0) as population')
            ->selectRaw('COALESCE(SUM(counties.registered_voters), 0) as registered_voters')
            ->first();

        $bloc->forceFill([
            'total_population' => (int) $totals->population,
            'total_registered_voters' => (int) $totals->registered_voters,
        ])->save();
    }
}