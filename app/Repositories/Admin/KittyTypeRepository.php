<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\KittyTypeRepositoryInterface;
use App\Models\KittyType;
use Illuminate\Support\Collection;

class KittyTypeRepository implements KittyTypeRepositoryInterface
{
    public function all(): Collection { return KittyType::withCount('purchases')->ordered()->get(); }
    public function active(): Collection { return KittyType::active()->ordered()->get(); }
    public function findActive(int $id): KittyType { return KittyType::active()->findOrFail($id); }
    public function create(array $data): KittyType { return KittyType::create($data); }
    public function update(KittyType $kittyType, array $data): bool { return $kittyType->update($data); }
    public function delete(KittyType $kittyType): bool { return $kittyType->delete(); }
    public function hasPurchases(KittyType $kittyType): bool { return $kittyType->purchases()->exists(); }
}
