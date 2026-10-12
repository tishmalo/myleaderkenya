<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\SupportGroupTypeRepositoryInterface;
use App\Models\SupportGroupType;
use Illuminate\Database\Eloquent\Collection;

class SupportGroupTypeRepository implements SupportGroupTypeRepositoryInterface
{
    public function allWithContactCountOrdered(): Collection
    {
        return SupportGroupType::withCount('contacts')->ordered()->get();
    }

    public function create(array $attributes): SupportGroupType
    {
        return SupportGroupType::create($attributes);
    }

    public function update(SupportGroupType $type, array $attributes): bool
    {
        return $type->update($attributes);
    }

    public function delete(SupportGroupType $type): bool
    {
        return $type->delete();
    }

    public function hasContacts(SupportGroupType $type): bool
    {
        return $type->contacts()->exists();
    }
}
