<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\CampaignWebsiteRepositoryInterface;
use App\Models\CampaignWebsiteRequest;
use App\Models\CampaignWebsiteSample;
use Illuminate\Support\Collection;

class CampaignWebsiteRepository implements CampaignWebsiteRepositoryInterface
{
    public function latestRequestFor(int $candidateId): ?CampaignWebsiteRequest
    {
        return CampaignWebsiteRequest::where('candidate_id', $candidateId)->latest()->first();
    }

    public function saveRequest(int $candidateId, array $attributes): CampaignWebsiteRequest
    {
        return CampaignWebsiteRequest::updateOrCreate(
            ['candidate_id' => $candidateId],
            $attributes
        );
    }

    public function publishedSamples(int $limit): Collection
    {
        return CampaignWebsiteSample::published()->ordered()->take($limit)->get();
    }

    public function allPublishedSamples(): Collection
    {
        return CampaignWebsiteSample::published()->ordered()->get();
    }
}
