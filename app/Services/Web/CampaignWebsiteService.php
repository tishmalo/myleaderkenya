<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\CampaignWebsiteRepositoryInterface;
use App\Models\Candidate;
use App\Models\CampaignWebsiteRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class CampaignWebsiteService
{
    public function __construct(
        private CampaignWebsiteRepositoryInterface $websites,
        private CampaignToolCommerceService $commerce,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function submit(Candidate $candidate, User $user, array $validated): CampaignWebsiteRequest
    {
        $request = $this->websites->saveRequest($candidate->id, array_merge($validated, [
            'user_id' => $user->id,
            'status' => 'new',
        ]));

        $this->commerce->consumeEntitlement($candidate->id, 'campaign-website');

        return $request;
    }

    public function samples(): Collection
    {
        return $this->websites->allPublishedSamples();
    }
}
