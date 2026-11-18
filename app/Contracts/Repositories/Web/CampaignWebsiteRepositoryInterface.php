<?php

namespace App\Contracts\Repositories\Web;

use App\Models\CampaignWebsiteRequest;
use App\Models\CampaignWebsiteSample;
use Illuminate\Support\Collection;

interface CampaignWebsiteRepositoryInterface
{
    public function latestRequestFor(int $candidateId): ?CampaignWebsiteRequest;

    public function saveRequest(int $candidateId, array $attributes): CampaignWebsiteRequest;

    public function publishedSamples(int $limit): Collection;

    public function allPublishedSamples(): Collection;
}
