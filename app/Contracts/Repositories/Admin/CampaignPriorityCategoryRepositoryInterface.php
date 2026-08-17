<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\CampaignPriorityCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CampaignPriorityCategoryRepositoryInterface
{
    public function categoriesWithUsageCount(): Collection;

    public function paginateSubmissions(array $filters, int $perPage): LengthAwarePaginator;

    public function create(array $data): CampaignPriorityCategory;

    public function update(CampaignPriorityCategory $category, array $data): bool;

    public function updatePrioritySortOrder(CampaignPriorityCategory $category, int $sortOrder): int;

    public function slugExists(string $slug, ?int $ignoreId = null): bool;

    public function hasPriorities(CampaignPriorityCategory $category): bool;

    public function delete(CampaignPriorityCategory $category): bool;
}
