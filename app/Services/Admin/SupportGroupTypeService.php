<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\SupportGroupTypeRepositoryInterface;
use App\Models\SupportGroupType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class SupportGroupTypeService
{
    public function __construct(
        private SupportGroupTypeRepositoryInterface $types
    ) {}

    public function list(): Collection
    {
        return $this->types->allWithContactCountOrdered();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{status: string, message: string}
     */
    public function create(array $data, bool $isActive): array
    {
        $this->types->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $isActive,
        ]);

        return ['status' => 'success', 'message' => 'Support group type added.'];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{status: string, message: string}
     */
    public function update(SupportGroupType $type, array $data, bool $isActive): array
    {
        $this->types->update($type, [
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $isActive,
        ]);

        return ['status' => 'success', 'message' => 'Support group type updated.'];
    }

    /**
     * @return array{status: string, message: string}
     */
    public function delete(SupportGroupType $type): array
    {
        if ($this->types->hasContacts($type)) {
            return ['status' => 'warning', 'message' => 'This group has contacts. Deactivate it instead of deleting it.'];
        }

        $this->types->delete($type);

        return ['status' => 'success', 'message' => 'Support group type deleted.'];
    }
}
