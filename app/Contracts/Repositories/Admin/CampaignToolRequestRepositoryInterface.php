<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\CampaignToolRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CampaignToolRequestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function toolOptions(): Collection;

    public function update(CampaignToolRequest $request, array $data): bool;

    public function delete(CampaignToolRequest $request): bool;
}
