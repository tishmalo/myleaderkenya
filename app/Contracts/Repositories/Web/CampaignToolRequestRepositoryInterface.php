<?php

namespace App\Contracts\Repositories\Web;

use App\Models\CampaignToolRequest;

interface CampaignToolRequestRepositoryInterface
{
    public function create(array $data): CampaignToolRequest;

    public function syncSelectedTools(CampaignToolRequest $request, array $toolIds): void;

    public function activeAdoptedToolIds(int $userId, int $candidateId, array $toolIds): array;

    public function updateAdoptionStatus(int $userId, int $candidateId, string $status): void;
}