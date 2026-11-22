<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Admin\CandidateSmsBalanceRequestRepositoryInterface;
use App\Contracts\Repositories\Web\AspirantPollRepositoryInterface;
use App\Contracts\Repositories\Web\CandidateCallCenterRepositoryInterface;
use App\Contracts\Repositories\Web\CandidateSupportContactRepositoryInterface;
use App\Contracts\Repositories\Web\CampaignWebsiteRepositoryInterface;
use App\Models\Candidate;
use App\Services\Sms\InfobipSmsService;
use App\Support\AspirantToolAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AspirantToolPageService
{
    public function __construct(
        private AspirantWorkspaceService $workspace,
        private AspirantTokenService $tokens,
        private CandidateBulkSmsContactService $bulkSmsContacts,
        private AspirantPollRepositoryInterface $polls,
        private CandidateCallCenterRepositoryInterface $callCenter,
        private CampaignWebsiteRepositoryInterface $websites,
        private CandidateSupportContactRepositoryInterface $supportContacts,
        private CandidateSmsBalanceRequestRepositoryInterface $smsBalanceRequests,
        private InfobipSmsService $smsService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function payload(AspirantToolAccess $access, Request $request): array
    {
        $candidate = $access->candidate;
        $key = (string) $access->key;
        $scope = $access->scope;
        $isBlocked = $access->voterFacing() && $access->scopeMissing() && $key !== 'bulk-sms';
        $voterQuery = $this->workspace->registeredVotersQuery($scope);

        $voterCount = $access->voterFacing() && ! $access->scopeMissing()
            ? (clone $voterQuery)->count()
            : 0;

        return [
            'candidate' => $candidate,
            'module' => array_merge((array) $access->definition, [
                'key' => $key,
                'tool' => $access->tool,
            ]),
            'scope' => $scope,
            'isBlocked' => $isBlocked,
            'voterCount' => $voterCount,
            'recentVoters' => $this->recentVoters($access, $voterQuery),
            'polls' => $key === 'opinion-polls' && $candidate
                ? $this->polls->recentForCandidate($candidate->id, 8)
                : collect(),
            'websiteRequest' => $key === 'campaign-website' && $candidate
                ? $this->websites->latestRequestFor($candidate->id)
                : null,
            'websiteSamples' => $key === 'campaign-website'
                ? $this->websites->publishedSamples(6)
                : collect(),
            'callScript' => $key === 'call-center' && $candidate
                ? $this->callCenter->scriptFor($candidate->id)
                : null,
            'callListActive' => $key === 'call-center' && $request->boolean('call_list'),
            'callListContacts' => $this->callListContacts($access, $request, $voterQuery),
            'callLogs' => $key === 'call-center' && $candidate
                ? $this->callCenter->logsFor($candidate->id, 12)
                : collect(),
            'tokenWallet' => $candidate ? $this->tokens->walletForCandidate($candidate) : null,
            'tokenRates' => $this->tokens->activeRates()->keyBy('action_key'),
            'bulkSmsQuote' => $key === 'bulk-sms' && ! $isBlocked
                ? $this->tokens->quoteBulkSms((string) old('message', ''), $voterCount)
                : null,
            'smsBalanceRequest' => $key === 'bulk-sms' && $candidate
                ? $this->smsBalanceRequests->latestForCandidate($candidate->id)
                : null,
            'smsProviderBalance' => $this->smsProviderBalance($access, $candidate),
            'supportGroupTypes' => $this->supportGroupTypes($key),
            'supportContacts' => $key === 'support-groups' && $candidate
                ? $this->supportContacts->forCandidate($candidate->id)
                : collect(),
            'uploadedContactCount' => $key === 'bulk-sms' && $candidate
                ? $this->bulkSmsContacts->recipientCount($candidate)
                : 0,
            'uploadedContactCounts' => $key === 'bulk-sms' && $candidate
                ? $this->bulkSmsContacts->countsByClassification($candidate)
                : collect(),
        ];
    }

    private function recentVoters(AspirantToolAccess $access, $voterQuery): Collection
    {
        if (! $access->voterFacing() || $access->scopeMissing()) {
            return collect();
        }

        return (clone $voterQuery)
            ->select('name', 'username', 'phone', 'county', 'constituency', 'ward', 'polling_station', 'created_at')
            ->latest()
            ->take(8)
            ->get();
    }

    private function callListContacts(AspirantToolAccess $access, Request $request, $voterQuery): Collection|LengthAwarePaginator
    {
        if ((string) $access->key !== 'call-center' || ! $request->boolean('call_list')) {
            return collect();
        }

        return (clone $voterQuery)
            ->whereNotNull('phone')
            ->select('id', 'name', 'username', 'phone', 'county', 'constituency', 'ward', 'polling_station', 'created_at')
            ->latest()
            ->paginate(10, ['*'], 'call_page')
            ->appends(['call_list' => 1])
            ->fragment('call-list');
    }

    private function smsProviderBalance(AspirantToolAccess $access, ?Candidate $candidate): ?array
    {
        if ((string) $access->key !== 'bulk-sms' || ! $candidate?->smsSetting?->isReady()) {
            return null;
        }

        return $this->smsService->accountBalance($candidate->smsSetting);
    }

    private function supportGroupTypes(string $key): Collection
    {
        return in_array($key, ['support-groups', 'bulk-sms'], true)
            ? $this->bulkSmsContacts->classifications()
            : collect();
    }
}
