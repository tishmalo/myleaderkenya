<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\KittyType;
use Illuminate\Support\Collection;

interface KittyTypeRepositoryInterface
{
    public function all(): Collection;
    public function active(): Collection;
    public function findActive(int $id): KittyType;
    public function create(array $data): KittyType;
    public function update(KittyType $kittyType, array $data): bool;
    public function delete(KittyType $kittyType): bool;
    public function hasPurchases(KittyType $kittyType): bool;
}
