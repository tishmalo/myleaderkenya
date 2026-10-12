<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\SupportGroupType;
use Illuminate\Database\Eloquent\Collection;

interface SupportGroupTypeRepositoryInterface
{
    public function allWithContactCountOrdered(): Collection;

    public function create(array $attributes): SupportGroupType;

    public function update(SupportGroupType $type, array $attributes): bool;

    public function delete(SupportGroupType $type): bool;

    public function hasContacts(SupportGroupType $type): bool;
}
