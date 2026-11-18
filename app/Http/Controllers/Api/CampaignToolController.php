<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListCampaignToolsRequest;
use App\Http\Requests\Api\SendBulkSmsRequest;
use App\Http\Requests\Api\StoreCampaignToolFeatureRequest;
use App\Http\Requests\Web\SaveCallScriptRequest;
use App\Http\Requests\Web\StoreAspirantPollRequest;
use App\Http\Requests\Web\StoreCallLogRequest;
use App\Http\Requests\Web\StoreCampaignWebsiteRequest;
use App\Http\Requests\Web\SupportContactRequest;
use App\Models\CandidateSupportContact;
use App\Models\CampaignTool;
use App\Services\Web\AspirantBulkSmsService;
use App\Services\Web\AspirantPollService;
use App\Services\Web\AspirantToolAccessService;
use App\Services\Web\AspirantToolApiService;
use App\Services\Web\CampaignToolApiService;
use App\Services\Web\CandidateCallCenterService;
use App\Services\Web\CandidateSupportContactService;
use App\Services\Web\CampaignWebsiteService;
use App\Support\AspirantToolAccess;
use App\Support\CampaignToolApiPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignToolController extends Controller
{
    public function __construct(
        private CampaignToolApiService $campaignTools,
        private AspirantToolApiService $aspirantTools,
        private AspirantToolAccessService $access,
        private AspirantBulkSmsService $bulkSms,
        private AspirantPollService $polls,
        private CandidateCallCenterService $callCenter,
        private CampaignWebsiteService $websites,
        private CandidateSupportContactService $supportContacts,
    ) {}

    public function list(ListCampaignToolsRequest $request): JsonResponse
    {
        return response()->json($this->campaignTools->paginate($request->perPage()));
    }

    public function show(CampaignTool $campaignTool): JsonResponse
    {
        return response()->json([
            'data' => $this->campaignTools->show($campaignTool),
        ]);
    }

    public function storeFeatureRequest(StoreCampaignToolFeatureRequest $request, CampaignTool $campaignTool): JsonResponse
    {
        $featureRequest = $this->campaignTools->submitFeatureRequest(
            $campaignTool,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Feature request submitted. The admin team will review it.',
            'data' => CampaignToolApiPresenter::featureRequest($featureRequest->load('campaignTool')),
        ], 201);
    }

    public function aspirantTools(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->aspirantTools->modules($request->user()),
        ]);
    }

    public function aspirantTool(Request $request, string $key): JsonResponse
    {
        $tool = $this->toolOrFail($request, $key);

        return response()->json([
            'data' => $this->aspirantTools->detail($tool),
        ]);
    }

    public function sendBulkSms(SendBulkSmsRequest $request): JsonResponse
    {
        $tool = $this->toolOrFail($request, 'bulk-sms', requireScope: true);

        $result = $this->bulkSms->send($tool->candidateOrFail(), $request->user(), [
            'message' => (string) $request->validated()['message'],
            'recipient_source' => 'registered_voters',
        ], $tool->scope);

        if ($result->failed()) {
            return response()->json(['message' => $result->warning], $result->status);
        }

        return response()->json([
            'message' => 'Bulk SMS queued.',
            'data' => $result->message,
            'quote' => $result->quote,
        ], 201);
    }

    public function storePoll(StoreAspirantPollRequest $request): JsonResponse
    {
        $tool = $this->toolOrFail($request, 'opinion-polls', requireScope: true);

        $poll = $this->polls->store(
            $tool->candidateOrFail(),
            $request->user(),
            (string) $request->validated()['question'],
            $request->optionLines(),
            $request->status(),
            $tool->scope
        );

        return response()->json([
            'message' => $poll->status === 'published' ? 'Poll published.' : 'Poll draft saved.',
            'data' => $poll->load('group'),
        ], 201);
    }

    public function saveCallScript(SaveCallScriptRequest $request): JsonResponse
    {
        $tool = $this->toolOrFail($request, 'call-center', requireScope: true);
        $validated = $request->validated();

        $script = $this->callCenter->saveScript(
            $tool->candidateOrFail(),
            $request->user(),
            (string) $validated['script'],
            (string) $validated['callback_priority'],
            $tool->scope
        );

        return response()->json([
            'message' => 'Call script saved.',
            'data' => $script,
        ]);
    }

    public function storeCallLog(StoreCallLogRequest $request): JsonResponse
    {
        $tool = $this->toolOrFail($request, 'call-center', requireScope: true);

        $callLog = $this->callCenter->recordCall($tool->candidateOrFail(), $request->user(), $request->validated(), $tool->scope);

        if (! $callLog) {
            return response()->json(['message' => 'That voter is not available in your scoped call list.'], 404);
        }

        return response()->json([
            'message' => 'Call log recorded.',
            'data' => $callLog,
        ], 201);
    }

    public function storeWebsiteRequest(StoreCampaignWebsiteRequest $request): JsonResponse
    {
        $tool = $this->toolOrFail($request, 'campaign-website');

        $websiteRequest = $this->websites->submit($tool->candidateOrFail(), $request->user(), $request->validated());

        return response()->json([
            'message' => 'Campaign website request submitted.',
            'data' => $websiteRequest,
        ], 201);
    }

    public function supportGroupTypes(): JsonResponse
    {
        return response()->json([
            'data' => $this->aspirantTools->supportGroupTypes(),
        ]);
    }

    public function supportContacts(Request $request): JsonResponse
    {
        $candidate = $this->aspirantTools->candidateOrFail($request->user());

        return response()->json([
            'data' => $this->aspirantTools->supportContacts($candidate->id),
        ]);
    }

    public function storeSupportContact(SupportContactRequest $request): JsonResponse
    {
        $tool = $this->toolOrFail($request, 'support-groups');

        $contact = $this->supportContacts->create($tool->candidateOrFail(), $request->user(), $request->validated());

        return response()->json([
            'message' => 'Support contact added.',
            'data' => CampaignToolApiPresenter::supportContact($contact->load('groupType')),
        ], 201);
    }

    public function updateSupportContact(SupportContactRequest $request, CandidateSupportContact $candidateSupportContact): JsonResponse
    {
        $candidate = $this->aspirantTools->candidateOrFail($request->user());

        $this->supportContacts->update($candidateSupportContact, $candidate, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Support contact updated.',
            'data' => CampaignToolApiPresenter::supportContact($candidateSupportContact->refresh()->load('groupType')),
        ]);
    }

    public function destroySupportContact(Request $request, CandidateSupportContact $candidateSupportContact): JsonResponse
    {
        $candidate = $this->aspirantTools->candidateOrFail($request->user());

        $this->supportContacts->delete($candidateSupportContact, $candidate);

        return response()->json(['message' => 'Support contact removed.']);
    }

    private function toolOrFail(Request $request, string $key, bool $requireScope = false): AspirantToolAccess
    {
        $tool = $this->access->resolve($request->user(), $key);

        abort_unless($tool->exists(), 404);

        if ($denial = $tool->denialMessage()) {
            abort(response()->json(['message' => $denial], $tool->candidate ? 403 : 404));
        }

        if ($requireScope && $tool->scopeMissing()) {
            abort(response()->json(['message' => $tool->scopeMessage()], 422));
        }

        return $tool;
    }
}
