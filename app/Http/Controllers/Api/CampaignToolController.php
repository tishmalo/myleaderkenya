<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\Web\CandidateSmsMessageRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Jobs\SendCandidateBulkSms;
use App\Models\AspirantPoll;
use App\Models\CampaignTool;
use App\Models\CampaignToolRequest;
use App\Models\CampaignWebsiteRequest;
use App\Models\CampaignWebsiteSample;
use App\Models\CandidateCallLog;
use App\Models\CandidateCallScript;
use App\Models\CandidateSupportContact;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Models\SupportGroupType;
use App\Services\Web\AspirantTokenService;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CampaignToolController extends Controller
{
    public function __construct(
        private AspirantWorkspaceService $workspaceService,
        private AspirantTokenService $tokenService,
        private CandidateSmsMessageRepositoryInterface $smsMessageRepository
    ) {}

    public function list(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 12), 50);

        $tools = CampaignTool::published()
            ->ordered()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (CampaignTool $tool) => $this->formatTool($tool, true));

        return response()->json($tools);
    }

    public function show(CampaignTool $campaignTool): JsonResponse
    {
        abort_unless($campaignTool->status === 'published', 404);

        return response()->json([
            'data' => $this->formatTool($campaignTool, true, true),
        ]);
    }

    public function storeFeatureRequest(Request $request, CampaignTool $campaignTool): JsonResponse
    {
        abort_unless($campaignTool->status === 'published', 404);

        $validated = $request->validate($this->featureRequestRules());

        if (blank($validated['email'] ?? null) && blank($validated['phone'] ?? null)) {
            throw ValidationException::withMessages([
                'phone' => 'Enter an email or phone so the team can follow up.',
            ]);
        }

        $user = $request->user('sanctum') ?: $request->user();
        $candidate = $user ? $this->workspaceService->candidateForUser($user) : null;

        $featureRequest = CampaignToolRequest::create($validated + [
            'campaign_tool_id' => $campaignTool->id,
            'user_id' => $user?->id,
            'candidate_id' => $candidate?->id,
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'Feature request submitted. The admin team will review it.',
            'data' => $this->formatFeatureRequest($featureRequest->load('campaignTool')),
        ], 201);
    }

    public function aspirantTools(Request $request): JsonResponse
    {
        $candidate = $this->candidateOrFail($request);
        $publishedTools = CampaignTool::published()->ordered()->get();
        $modules = $this->workspaceService->toolModules($publishedTools, $candidate);

        return response()->json([
            'data' => collect($modules)->map(fn (array $module) => $this->formatToolModule($module))->values(),
        ]);
    }

    public function aspirantTool(Request $request, string $key): JsonResponse
    {
        $candidate = $this->candidateOrFail($request);
        $definitions = $this->workspaceService->toolDefinitions();

        abort_unless(isset($definitions[$key]), 404);

        $availability = $this->workspaceService->canUseTool($key, $candidate);
        if (! $availability['available']) {
            return response()->json(['message' => $availability['reason']], 403);
        }

        $scope = $this->workspaceService->scopeForCandidate($candidate);
        $voterCount = $definitions[$key]['voter_facing'] && ! $scope['missing']
            ? $this->workspaceService->registeredVotersQuery($scope)->count()
            : null;

        return response()->json([
            'data' => [
                'key' => $key,
                'definition' => $definitions[$key],
                'tool' => $this->optionalTool($this->workspaceService->publishedToolForKey($key)),
                'availability' => $availability,
                'scope' => $scope,
                'voter_count' => $voterCount,
                'wallet' => $this->formatWallet($this->tokenService->walletForCandidate($candidate)),
                'rates' => $this->tokenService->activeRates()->values(),
                'recent_polls' => $key === 'opinion-polls' ? $this->recentPolls($candidate->id) : [],
                'website_request' => $key === 'campaign-website' ? $this->formatWebsiteRequest(CampaignWebsiteRequest::where('candidate_id', $candidate->id)->latest()->first()) : null,
                'website_samples' => $key === 'campaign-website' ? CampaignWebsiteSample::published()->ordered()->get() : [],
                'call_script' => $key === 'call-center' ? CandidateCallScript::where('candidate_id', $candidate->id)->first() : null,
                'support_group_types' => $key === 'support-groups' ? $this->supportGroupTypesPayload() : [],
                'support_contacts' => $key === 'support-groups' ? $this->supportContactsPayload($candidate->id) : [],
            ],
        ]);
    }

    public function sendBulkSms(Request $request): JsonResponse
    {
        $candidate = $this->candidateOrFail($request);
        $this->assertToolAvailable('bulk-sms', $candidate);
        $scope = $this->scopeOrFail($candidate);

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:918'],
        ]);

        $recipientCount = $this->workspaceService->registeredVotersQuery($scope)->whereNotNull('phone')->count();
        $quote = $this->tokenService->quoteBulkSms($validated['message'], $recipientCount);

        if ($quote['sms_units'] <= 0) {
            return response()->json(['message' => 'No valid phone numbers were found for your scoped voters.'], 422);
        }

        try {
            $reservation = $this->tokenService->reserveBulkSms($candidate, $request->user(), $quote);
            $smsMessage = $this->smsMessageRepository->create([
                'candidate_id' => $candidate->id,
                'user_id' => $request->user()->id,
                'message' => $validated['message'],
                'scope_type' => $scope['type'],
                'scope_column' => $scope['column'],
                'scope_value' => $scope['value'],
                'recipient_count' => $recipientCount,
                'status' => 'queued',
                'token_transaction_id' => $reservation->id,
                'sms_character_count' => $quote['character_count'],
                'sms_encoding' => $quote['encoding'],
                'sms_segment_count' => $quote['segment_count'],
                'sms_unit_count' => $quote['sms_units'],
                'token_cost' => $quote['tokens_required'],
            ]);
            $this->tokenService->attachReservationToSms($reservation, $smsMessage);
        } catch (RuntimeException $exception) {
            if (isset($reservation)) {
                $this->tokenService->refundReservation($reservation, 'Bulk SMS could not be queued.');
            }

            return response()->json(['message' => $exception->getMessage() . ' Buy more tokens to continue.'], 402);
        }

        SendCandidateBulkSms::dispatch($smsMessage->id);

        return response()->json([
            'message' => 'Bulk SMS queued.',
            'data' => $smsMessage,
            'quote' => $quote,
        ], 201);
    }

    public function storePoll(Request $request): JsonResponse
    {
        $candidate = $this->candidateOrFail($request);
        $this->assertToolAvailable('opinion-polls', $candidate);
        $scope = $this->scopeOrFail($candidate);

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'options' => ['required'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        $options = $this->pollOptions($validated['options']);
        if ($options->count() < 2) {
            return response()->json(['message' => 'Add at least two poll options.'], 422);
        }

        $status = $validated['status'] ?? 'draft';
        $quote = $status === 'published'
            ? $this->tokenService->quoteFixed('poll-publish', $this->workspaceService->registeredVotersQuery($scope)->count())
            : $this->tokenService->quoteFixed('poll-draft');

        try {
            $poll = DB::transaction(function () use ($request, $candidate, $scope, $validated, $options, $status, $quote): AspirantPoll {
                $group = null;

                if ($status === 'published') {
                    $group = $this->scopedPollGroup($request, $scope);

                    GroupMember::firstOrCreate(['group_id' => $group->id, 'user_id' => $request->user()->id]);
                    $this->workspaceService->registeredVotersQuery($scope)->select('id')->orderBy('id')->chunkById(200, function ($voters) use ($group): void {
                        foreach ($voters as $voter) {
                            GroupMember::firstOrCreate(['group_id' => $group->id, 'user_id' => $voter->id]);
                        }
                    });
                }

                $poll = AspirantPoll::create([
                    'candidate_id' => $candidate->id,
                    'user_id' => $request->user()->id,
                    'group_id' => $group?->id,
                    'question' => $validated['question'],
                    'options' => $options->all(),
                    'scope_type' => $scope['type'],
                    'scope_column' => $scope['column'],
                    'scope_value' => $scope['value'],
                    'status' => $status,
                    'published_at' => $status === 'published' ? now() : null,
                ]);

                $this->tokenService->debitAction($candidate, $request->user(), $quote, $poll);

                if ($group) {
                    GroupMessage::create([
                        'group_id' => $group->id,
                        'username' => $request->user()->username ?? $request->user()->name ?? 'Aspirant',
                        'message' => $this->pollMessage($poll),
                        'message_type' => 'poll',
                        'aspirant_poll_id' => $poll->id,
                        'latitude' => null,
                        'longitude' => null,
                    ]);
                }

                return $poll;
            });
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage() . ' Buy more tokens to continue.'], 402);
        }

        return response()->json([
            'message' => $status === 'published' ? 'Poll published.' : 'Poll draft saved.',
            'data' => $poll->load('group'),
            'quote' => $quote,
        ], 201);
    }

    public function saveCallScript(Request $request): JsonResponse
    {
        $candidate = $this->candidateOrFail($request);
        $this->assertToolAvailable('call-center', $candidate);
        $scope = $this->scopeOrFail($candidate);

        $validated = $request->validate([
            'script' => ['required', 'string', 'min:20', 'max:5000'],
            'callback_priority' => ['required', 'in:undecided,supporters,volunteers'],
        ]);

        $quote = $this->tokenService->quoteFixed('call-script-save');

        try {
            $script = CandidateCallScript::updateOrCreate(
                ['candidate_id' => $candidate->id],
                $validated + [
                    'user_id' => $request->user()->id,
                    'scope_type' => $scope['type'],
                    'scope_column' => $scope['column'],
                    'scope_value' => $scope['value'],
                ]
            );
            $this->tokenService->debitAction($candidate, $request->user(), $quote, $script);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage() . ' Buy more tokens to continue.'], 402);
        }

        return response()->json(['message' => 'Call script saved.', 'data' => $script, 'quote' => $quote]);
    }

    public function storeCallLog(Request $request): JsonResponse
    {
        $candidate = $this->candidateOrFail($request);
        $this->assertToolAvailable('call-center', $candidate);
        $scope = $this->scopeOrFail($candidate);

        $validated = $request->validate([
            'voter_user_id' => ['required', 'integer'],
            'outcome' => ['required', 'in:reached,no_answer,busy,wrong_number,callback,not_interested,supporter,volunteer'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'callback_at' => ['nullable', 'date'],
        ]);

        $voter = $this->workspaceService->registeredVotersQuery($scope)
            ->whereKey($validated['voter_user_id'])
            ->whereNotNull('phone')
            ->first();

        if (! $voter) {
            return response()->json(['message' => 'That voter is not available in your scoped call list.'], 404);
        }

        $quote = $this->tokenService->quoteFixed('call-log');

        try {
            $callLog = DB::transaction(function () use ($candidate, $request, $voter, $validated, $scope, $quote): CandidateCallLog {
                $callLog = CandidateCallLog::create([
                    'candidate_id' => $candidate->id,
                    'user_id' => $request->user()->id,
                    'voter_user_id' => $voter->id,
                    'voter_name' => $voter->name ?: $voter->username,
                    'voter_phone' => $voter->phone,
                    'outcome' => $validated['outcome'],
                    'notes' => $validated['notes'] ?? null,
                    'callback_at' => $validated['callback_at'] ?? null,
                    'scope_type' => $scope['type'],
                    'scope_column' => $scope['column'],
                    'scope_value' => $scope['value'],
                    'called_at' => now(),
                ]);

                $this->tokenService->debitAction($candidate, $request->user(), $quote, $callLog);

                return $callLog;
            });
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage() . ' Buy more tokens to continue.'], 402);
        }

        return response()->json(['message' => 'Call log recorded.', 'data' => $callLog, 'quote' => $quote], 201);
    }

    public function storeWebsiteRequest(Request $request): JsonResponse
    {
        $candidate = $this->candidateOrFail($request);

        $validated = $request->validate([
            'candidate_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'preferred_domain' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9 ._-]+(\.[a-zA-Z]{2,})?$/'],
            'website_type' => ['required', 'in:standard,premium,custom'],
            'reference_url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $quote = $this->tokenService->quoteFixed('campaign-website-request');

        try {
            $websiteRequest = CampaignWebsiteRequest::updateOrCreate(
                ['candidate_id' => $candidate->id],
                $validated + ['user_id' => $request->user()->id, 'status' => 'new']
            );
            $this->tokenService->debitAction($candidate, $request->user(), $quote, $websiteRequest);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage() . ' Buy more tokens to continue.'], 402);
        }

        return response()->json(['message' => 'Campaign website request submitted.', 'data' => $websiteRequest, 'quote' => $quote], 201);
    }

    public function supportGroupTypes(): JsonResponse
    {
        return response()->json(['data' => $this->supportGroupTypesPayload()]);
    }

    public function supportContacts(Request $request): JsonResponse
    {
        $candidate = $this->candidateOrFail($request);

        return response()->json(['data' => $this->supportContactsPayload($candidate->id)]);
    }

    public function storeSupportContact(Request $request): JsonResponse
    {
        $candidate = $this->candidateOrFail($request);
        $validated = $this->validateSupportContact($request);

        $contact = $candidate->supportContacts()->create($validated + [
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Support contact added.', 'data' => $this->formatSupportContact($contact->load('groupType'))], 201);
    }

    public function updateSupportContact(Request $request, CandidateSupportContact $candidateSupportContact): JsonResponse
    {
        $candidate = $this->candidateOrFail($request);
        abort_unless((int) $candidateSupportContact->candidate_id === (int) $candidate->id, 403);

        $candidateSupportContact->update($this->validateSupportContact($request) + [
            'updated_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Support contact updated.', 'data' => $this->formatSupportContact($candidateSupportContact->refresh()->load('groupType'))]);
    }

    public function destroySupportContact(Request $request, CandidateSupportContact $candidateSupportContact): JsonResponse
    {
        $candidate = $this->candidateOrFail($request);
        abort_unless((int) $candidateSupportContact->candidate_id === (int) $candidate->id, 403);

        $candidateSupportContact->delete();

        return response()->json(['message' => 'Support contact removed.']);
    }

    private function candidateOrFail(Request $request)
    {
        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            abort(response()->json(['message' => 'No aspirant profile is linked to this account yet.'], 404));
        }

        return $candidate;
    }

    private function assertToolAvailable(string $key, $candidate): void
    {
        $availability = $this->workspaceService->canUseTool($key, $candidate);

        if (! $availability['available']) {
            abort(response()->json(['message' => $availability['reason']], 403));
        }
    }

    private function scopeOrFail($candidate): array
    {
        $scope = $this->workspaceService->scopeForCandidate($candidate);

        if ($scope['missing']) {
            abort(response()->json(['message' => $scope['message']], 422));
        }

        return $scope;
    }

    private function featureRequestRules(): array
    {
        return [
            'requester_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'requested_feature' => ['required', 'string', 'max:255'],
            'use_case' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function validateSupportContact(Request $request): array
    {
        $validated = $request->validate([
            'support_group_type_id' => ['required', 'exists:support_group_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
        ]);

        if (blank($validated['email'] ?? null) && blank($validated['phone'] ?? null)) {
            throw ValidationException::withMessages(['phone' => 'Enter an email or phone for the support contact.']);
        }

        if (! SupportGroupType::active()->whereKey($validated['support_group_type_id'])->exists()) {
            throw ValidationException::withMessages(['support_group_type_id' => 'Choose an active support group.']);
        }

        return $validated;
    }

    private function pollOptions(mixed $value)
    {
        if (is_array($value)) {
            return collect($value)->map(fn ($option): string => trim((string) $option))->filter()->values();
        }

        return collect(preg_split('/\r\n|\r|\n/', (string) $value))->map(fn (string $option): string => trim($option))->filter()->values();
    }

    private function scopedPollGroup(Request $request, array $scope): Group
    {
        $name = $scope['label'] . ' Opinion Polls';
        $group = Group::where('name', $name)->where('created_by', $request->user()->id)->first();

        if ($group) {
            return $group;
        }

        return Group::create([
            'name' => $name,
            'description' => 'Opinion polls for voters in ' . $scope['label'] . '.',
            'created_by' => $request->user()->id,
            'invite_code' => $this->uniqueInviteCode(),
        ]);
    }

    private function uniqueInviteCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Group::where('invite_code', $code)->exists());

        return $code;
    }

    private function pollMessage(AspirantPoll $poll): string
    {
        $options = collect($poll->options)->map(fn (string $option, int $index): string => ($index + 1) . '. ' . $option)->implode("\n");

        return "[POLL #{$poll->id}]\n{$poll->question}\n{$options}";
    }

    private function recentPolls(int $candidateId): array
    {
        return AspirantPoll::with(['group', 'responses'])->where('candidate_id', $candidateId)->latest()->take(8)->get()->all();
    }

    private function supportGroupTypesPayload()
    {
        return SupportGroupType::active()->ordered()->get()->map(fn (SupportGroupType $type) => [
            'id' => $type->id,
            'name' => $type->name,
            'slug' => $type->slug,
            'sort_order' => $type->sort_order,
        ])->values();
    }

    private function supportContactsPayload(int $candidateId)
    {
        return CandidateSupportContact::with('groupType')->where('candidate_id', $candidateId)->latest()->get()->map(fn (CandidateSupportContact $contact) => $this->formatSupportContact($contact))->values();
    }

    private function formatSupportContact(CandidateSupportContact $contact): array
    {
        return [
            'id' => $contact->id,
            'support_group_type_id' => $contact->support_group_type_id,
            'support_group_type' => $contact->groupType ? [
                'id' => $contact->groupType->id,
                'name' => $contact->groupType->name,
                'slug' => $contact->groupType->slug,
            ] : null,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'created_at' => optional($contact->created_at)->toISOString(),
            'updated_at' => optional($contact->updated_at)->toISOString(),
        ];
    }

    private function formatToolModule(array $module): array
    {
        return [
            'key' => $module['key'],
            'title' => $module['title'],
            'icon' => $module['icon'],
            'summary' => $module['summary'],
            'voter_facing' => $module['voter_facing'],
            'available' => $module['available'],
            'disabled_reason' => $module['disabled_reason'],
            'tool' => $this->optionalTool($module['tool']),
        ];
    }

    private function optionalTool(?CampaignTool $tool): ?array
    {
        return $tool ? $this->formatTool($tool) : null;
    }

    private function formatTool(CampaignTool $tool, bool $includeExcerpt = false, bool $includeContent = false): array
    {
        $data = [
            'id' => $tool->id,
            'title' => $tool->title,
            'slug' => $tool->slug,
            'nav_label' => $tool->nav_label,
            'nav_title' => $tool->nav_title,
            'featured_image' => $tool->featured_image,
            'featured_image_url' => $tool->featured_image ? asset(Storage::url($tool->featured_image)) : null,
            'status' => $tool->status,
            'sort_order' => $tool->sort_order,
        ];

        if ($includeExcerpt) {
            $data['excerpt'] = $tool->excerpt;
            $data['meta_title'] = $tool->meta_title;
            $data['meta_description'] = $tool->meta_description;
        }

        if ($includeContent) {
            $data['content'] = $tool->content;
        }

        return $data;
    }

    private function formatFeatureRequest(CampaignToolRequest $request): array
    {
        return [
            'id' => $request->id,
            'campaign_tool_id' => $request->campaign_tool_id,
            'campaign_tool' => $this->optionalTool($request->campaignTool),
            'requester_name' => $request->requester_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'requested_feature' => $request->requested_feature,
            'use_case' => $request->use_case,
            'status' => $request->status,
            'created_at' => optional($request->created_at)->toISOString(),
        ];
    }

    private function formatWebsiteRequest(?CampaignWebsiteRequest $request): ?array
    {
        if (! $request) {
            return null;
        }

        return [
            'id' => $request->id,
            'candidate_name' => $request->candidate_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'preferred_domain' => $request->preferred_domain,
            'website_type' => $request->website_type,
            'reference_url' => $request->reference_url,
            'notes' => $request->notes,
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'created_at' => optional($request->created_at)->toISOString(),
            'updated_at' => optional($request->updated_at)->toISOString(),
        ];
    }

    private function formatWallet($wallet): array
    {
        return [
            'id' => $wallet->id,
            'candidate_id' => $wallet->candidate_id,
            'balance' => $wallet->balance,
            'initial_granted_at' => optional($wallet->initial_granted_at)->toISOString(),
        ];
    }
}

