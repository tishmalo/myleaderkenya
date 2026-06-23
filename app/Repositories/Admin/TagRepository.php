<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\TagRepositoryInterface;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagRepository implements TagRepositoryInterface
{
    public function getAllTags(): Collection
    {
        return Tag::orderBy('name')->get();
    }
    
    public function createTag(array $data): Tag
    {
        return Tag::create($data);
    }
    
    public function findById($id): ?Tag
    {
        return Tag::find($id);
    }
    
    public function deleteTag(Tag $tag): bool
    {
        return $tag->delete();
    }
}
