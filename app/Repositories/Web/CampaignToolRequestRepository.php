<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\CampaignToolRequestRepositoryInterface;
use App\Models\CampaignToolRequest;

class CampaignToolRequestRepository implements CampaignToolRequestRepositoryInterface
{
    public function create(array $data): CampaignToolRequest
    {
        return CampaignToolRequest::create($data);
    }
}
