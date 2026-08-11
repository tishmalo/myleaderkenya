<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\KittyTypeRepositoryInterface;
use App\Models\KittyType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KittyTypeService
{
    public function __construct(private KittyTypeRepositoryInterface $types) {}
    public function all(): Collection { return $this->types->all(); }
    public function active(): Collection { return $this->types->active(); }
    public function findActive(int $id): KittyType { return $this->types->findActive($id); }
    public function create(array $data): KittyType { return $this->types->create($this->payload($data)); }
    public function update(KittyType $kittyType, array $data): bool { return $this->types->update($kittyType, $this->payload($data)); }
    public function delete(KittyType $kittyType): bool
    {
        if ($this->types->hasPurchases($kittyType)) {
            throw ValidationException::withMessages(['kitty_type' => 'This kitty type has purchase history. Deactivate it instead of deleting it.']);
        }
        return $this->types->delete($kittyType);
    }
    private function payload(array $data): array
    {
        return [
            'name' => trim($data['name']),
            'slug' => Str::slug($data['name'], '_'),
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}
