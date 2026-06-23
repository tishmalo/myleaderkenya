<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Bloc;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BlocRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Bloc;

    public function update(Bloc $bloc, array $data): bool;

    public function delete(Bloc $bloc): bool;

    public function import(array $blocs): int;

    public function all(): Collection;
}
