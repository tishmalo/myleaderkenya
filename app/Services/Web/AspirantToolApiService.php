<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\AspirantPollRepositoryInterface;
use App\Contracts\Repositories\Web\CandidateCallCenterRepositoryInterface;
use App\Contracts\Repositories\Web\CandidateSupportContactRepositoryInterface;
use App\Contracts\Repositories\Web\CampaignWebsiteRepositoryInterface;
use App\Models\Candidate;
use App\Models\CandidateSupportContact;
use App\Models\SupportGroupType;
use App\Models\User;
use App\Services\Admin\CampaignToolService;
use App\Support\AspirantToolAccess;
use App\Support\CampaignToolApiPresenter;
use Illuminate\Support\Collection;

class AspirantToolApiService
{
    public function __construct(
        private AspirantWorkspaceService $workspace,
        private AspirantTokenService $tokens,
        private CampaignToolService $campaignTools,
        private AspirantPollRepositoryInterface $polls,
        private CandidateCallCenterRepositoryInterface $callCenter,
        private CampaignWebsiteRepositoryInterface $websites,
        private CandidateSupportContactRepositoryInterface $supportContacts,
        private CandidateBulkSmsContactService $classifications,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function modules(User $user): Collection
    {
        $candidate = $this->workspace->candidateForUser($user);

        return collect($this->workspace->toolModules($this->campaignTools->getPublishedNavTools(), $candidate))
            ->map(fn (array $module): array => CampaignToolApiPresenter::module($module))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(AspirantToolAccess $tool): array
    {
        $candidate = $tool->candidateOrFail();

        $key = (string) $tool->key;
        $scope = $tool->scope;

        return [
            'key' => $key,
            'definition' => $tool->definition,
            'tool' => CampaignToolApiPresenter::optionalTool($tool->tool),
            'availability' => $this->workspace->canUseTool($key, $candidate),
            'scope' => $scope,
            'voter_count' => $tool->voterFacing() && ! $tool->scopeMissing()
                ? $this->workspace->registeredVotersQuery($scope)->count()
                : null,
            'wallet' => CampaignToolApiPresenter::wallet($this->tokens->walletForCandidate($candidate)),
            'rates' => $this->tokens->activeRates()->values(),
            'recent_polls' => $key === 'opinion-polls'
                ? $this->polls->recentForCandidate($candidate->id, 8)
                : [],
            'website_request' => $key === 'campaign-website'
                ? CampaignToolApiPresenter::websiteRequest($this->websites->latestRequestFor($candidate->id))
                : null,
            'website_samples' => $key === 'campaign-website'
                ? $this->websites->allPublishedSamples()
                : [],
            'call_script' => $key === 'call-center'
                ? $this->callCenter->scriptFor($candidate->id)
                : null,
            'support_group_types' => $key === 'support-groups' ? $this->supportGroupTypes() : [],
            'support_contacts' => $key === 'support-groups' ? $this->supportContacts($candidate->id) : [],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function supportGroupTypes(): Collection
    {
        return $this->classifications->classifications()
            ->map(fn (SupportGroupType $type): array => CampaignToolApiPresenter::supportGroupType($type))
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function supportContacts(int $candidateId): Collection
    {
        return $this->supportContacts->forCandidate($candidateId)
            ->map(fn (CandidateSupportContact $contact): array => CampaignToolApiPresenter::supportContact($contact))
            ->values();
    }

    public function candidateOrFail(User $user): Candidate
    {
        $candidate = $this->workspace->candidateForUser($user);

        if (! $candidate) {
            abort(response()->json(['message' => 'No aspirant profile is linked to this account yet.'], 404));
        }

        return $candidate;
    }
}
