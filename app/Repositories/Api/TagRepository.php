<?php

namespace App\Repositories\Api;

use App\Contracts\Repositories\Api\TagRepositoryInterface;
use App\Models\Tag;
use Illuminate\Support\Collection;

class TagRepository implements TagRepositoryInterface
{
    public function all(): Collection
    {
        return Tag::select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();
    }
}
