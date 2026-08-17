<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CampaignPriorityCategoryRepositoryInterface;
use App\Models\CampaignPriorityCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CampaignPriorityCategoryService
{
    public function __construct(
        private CampaignPriorityCategoryRepositoryInterface $categories,
    ) {}

    public function indexData(array $filters): array
    {
        return [
            'categories' => $this->categories->categoriesWithUsageCount(),
            'submissions' => $this->categories->paginateSubmissions($filters, 30),
        ];
    }

    public function create(array $data, int $actorId): CampaignPriorityCategory
    {
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;

        return $this->categories->create($data);
    }

    public function update(
        CampaignPriorityCategory $category,
        array $data,
        int $actorId,
    ): void {
        if ($category->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], (int) $category->id);
        }

        $data['updated_by'] = $actorId;

        DB::transaction(function () use ($category, $data): void {
            $this->categories->update($category, $data);
            $this->categories->updatePrioritySortOrder($category, (int) $data['sort_order']);
        });
    }

    public function delete(CampaignPriorityCategory $category): void
    {
        if ($this->categories->hasPriorities($category)) {
            throw ValidationException::withMessages([
                'category' => 'Deactivate this category instead; aspirants have already used it.',
            ]);
        }

        $this->categories->delete($category);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'campaign-priority';
        $slug = $base;
        $suffix = 2;

        while ($this->categories->slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
