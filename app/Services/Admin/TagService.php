<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\TagRepositoryInterface;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class TagService
{
    public function __construct(
        private TagRepositoryInterface $tagRepository
    ) {}

    public function getAllTags(): Collection
    {
        return $this->tagRepository->getAllTags();
    }

    public function createTag(array $data): Tag
    {
        return $this->tagRepository->createTag([
            'name'        => trim($data['name']),
            'slug'        => trim($data['slug']),
            'description' => $data['description'] ?? null,
        ]);
    }

    public function deleteTag($id): array
    {
        $tag = $this->tagRepository->findById($id);

        if (!$tag) {
            return [
                'success' => false,
                'message' => 'Tag not found'
            ];
        }

        try {
            $this->tagRepository->deleteTag($tag);
            return [
                'success' => true,
                'message' => 'Tag deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete tag: ' . $e->getMessage()
            ];
        }
    }
}
