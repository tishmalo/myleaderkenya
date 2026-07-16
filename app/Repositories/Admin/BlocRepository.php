<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\BlocRepositoryInterface;
use App\Models\Bloc;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BlocRepository implements BlocRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->canonicalQuery()
            ->withCount('counties')
            ->paginate($perPage);
    }

    public function create(array $data): Bloc
    {
        return Bloc::create($data);
    }

    public function update(Bloc $bloc, array $data): bool
    {
        return $bloc->update($data);
    }

    public function delete(Bloc $bloc): bool
    {
        return $bloc->delete();
    }

    public function import(array $blocs): int
    {
        $imported = 0;

        foreach ($blocs as $blocData) {
            Bloc::updateOrCreate(
                ['name' => $blocData['name']],
                [
                    'tribes' => $blocData['tribes'] ?? null,
                    'tribe_population' => $blocData['tribe_population'] ?? null,
                    'voting_patterns' => $blocData['voting_patterns'] ?? null,
                ]
            );
            $imported++;
        }

        return $imported;
    }

    public function all(): Collection
    {
        return $this->canonicalQuery()->get();
    }

    private function canonicalQuery(): Builder
    {
        $names = config('regional-blocs.names', []);

        $query = Bloc::query();

        if ($names !== []) {
            $quotedNames = collect($names)
                ->map(fn (string $name) => "'" . str_replace("'", "''", $name) . "'")
                ->implode(',');

            $query->whereIn('name', $names)
                ->orderByRaw("FIELD(name, {$quotedNames})");
        } else {
            $query->orderBy('name');
        }

        return $query;
    }
}
