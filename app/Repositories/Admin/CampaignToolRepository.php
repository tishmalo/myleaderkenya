<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CampaignToolRepositoryInterface;
use App\Models\CampaignTool;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CampaignToolRepository implements CampaignToolRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CampaignTool::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('content', 'like', "%{$filters['search']}%");
            });
        }

        return $query->ordered()->paginate($perPage);
    }

    public function published(int $perPage = 12): LengthAwarePaginator
    {
        return CampaignTool::published()->ordered()->paginate($perPage);
    }

    public function publishedForNav(): Collection
    {
        return CampaignTool::published()->ordered()->get();
    }

    public function findPublishedBySlug(string $slug): CampaignTool
    {
        return CampaignTool::published()->where('slug', $slug)->firstOrFail();
    }

    public function create(array $data): CampaignTool
    {
        return CampaignTool::create($data);
    }

    public function update(CampaignTool $campaignTool, array $data): bool
    {
        return $campaignTool->update($data);
    }

    public function delete(CampaignTool $campaignTool): bool
    {
        return $campaignTool->delete();
    }
}