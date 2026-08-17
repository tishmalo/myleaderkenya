<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CampaignPriorityCategoryRepositoryInterface;
use App\Models\CampaignPriorityCategory;
use App\Models\CandidateCampaignPriority;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CampaignPriorityCategoryRepository implements CampaignPriorityCategoryRepositoryInterface
{
    public function categoriesWithUsageCount(): Collection
    {
        return CampaignPriorityCategory::query()
            ->withCount('candidatePriorities')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function paginateSubmissions(array $filters, int $perPage): LengthAwarePaginator
    {
        return CandidateCampaignPriority::query()
            ->with(['candidate:id,name,slug', 'category:id,name,icon', 'submitter:id,name'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['candidate'] ?? null, function ($query, $candidate): void {
                $query->whereHas('candidate', fn ($candidateQuery) => $candidateQuery
                    ->where('name', 'like', '%'.$candidate.'%'));
            })
            ->latest('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): CampaignPriorityCategory
    {
        return CampaignPriorityCategory::create($data);
    }

    public function update(CampaignPriorityCategory $category, array $data): bool
    {
        return $category->update($data);
    }

    public function updatePrioritySortOrder(CampaignPriorityCategory $category, int $sortOrder): int
    {
        return $category->candidatePriorities()->update(['sort_order' => $sortOrder]);
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return CampaignPriorityCategory::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function hasPriorities(CampaignPriorityCategory $category): bool
    {
        return $category->candidatePriorities()->exists();
    }

    public function delete(CampaignPriorityCategory $category): bool
    {
        return (bool) $category->delete();
    }
}
