<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\CampaignTool;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CampaignToolRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function published(int $perPage = 12): LengthAwarePaginator;

    public function publishedForNav(): Collection;

    public function findPublishedBySlug(string $slug): CampaignTool;

    public function create(array $data): CampaignTool;

    public function update(CampaignTool $campaignTool, array $data): bool;

    public function delete(CampaignTool $campaignTool): bool;
}