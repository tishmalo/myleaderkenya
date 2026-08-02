<?php

namespace App\Contracts\Repositories\Web;

use App\Models\CampaignToolRequest;

interface CampaignToolRequestRepositoryInterface
{
    public function create(array $data): CampaignToolRequest;
}
