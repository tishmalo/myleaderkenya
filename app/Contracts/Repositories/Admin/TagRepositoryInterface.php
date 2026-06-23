<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

interface TagRepositoryInterface
{
    public function getAllTags(): Collection;
    
    public function createTag(array $data): Tag;
    
    public function findById($id): ?Tag;
    
    public function deleteTag(Tag $tag): bool;
}
