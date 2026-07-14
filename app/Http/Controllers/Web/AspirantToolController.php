<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\Web\CandidateSmsMessageRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Jobs\SendCandidateBulkSms;
use App\Http\Requests\Web\SendBulkSmsRequest;
use App\Models\AspirantPoll;
use App\Models\CandidateCallLog;
use App\Models\CandidateCallScript;
use App\Models\CampaignWebsiteRequest;
use App\Models\CampaignWebsiteSample;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AspirantToolController extends Controller
{
    public function __construct(
        private AspirantWorkspaceService $workspaceService,
        private CandidateSmsMessageRepositoryInterface $smsMessageRepository
    ) {}

    public function show(Request $request, string $key): View|RedirectResponse
    {
        $definitions = $this->workspaceService->toolDefinitions();

        if (! isset($definitions[$key])) {
            abort(404);
        }

        $tool = $this->workspaceService->publishedToolForKey($key);

        if (! $tool && $key !== 'campaign-website') {
            return redirect('/aspirant/dashboard')
                ->with('warning', 'That campaign tool is not enabled yet. Ask an admin to publish it first.');
        }

        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect('/aspirant/dashboard')
                ->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $availability = $this->workspaceService->canUseTool($key, $candidate);

        if (! $availability['available']) {
            return redirect('/aspirant/dashboard')
                ->with('warning', $availability['reason']);
        }

        $module = array_merge($definitions[$key], [
            'key' => $key,
            'tool' => $tool,
        ]);
        $scope = $this->workspaceService->scopeForCandidate($candidate);
        $isBlocked = $module['voter_facing'] && $scope['missing'];
        $voterQuery = $this->workspaceService->registeredVotersQuery($scope);
        $voterCount = $module['voter_facing'] && ! $isBlocked ? (clone $voterQuery)->count() : null;
        $recentVoters = $module['voter_facing'] && ! $isBlocked
            ? (clone $voterQuery)
                ->select('name', 'username', 'phone', 'county', 'constituency', 'ward', 'polling_station', 'created_at')
                ->latest()
                ->take(8)
                ->get()
            : collect();
        $polls = $key === 'opinion-polls'
            ? AspirantPoll::with(['group', 'responses'])
                ->where('candidate_id', $candidate->id)
                ->latest()
                ->take(8)
                ->get()
            : collect();
        $websiteRequest = $key === 'campaign-website'
            ? CampaignWebsiteRequest::where('candidate_id', $candidate->id)->latest()->first()
            : null;
        $websiteSamples = $key === 'campaign-website'
            ? CampaignWebsiteSample::published()->ordered()->take(6)->get()
            : collect();
        $callScript = $key === 'call-center'
            ? CandidateCallScript::where('candidate_id', $candidate->id)->first()
            : null;
        $callListActive = $key === 'call-center' && $request->boolean('call_list');
        $callListContacts = $callListActive
            ? (clone $voterQuery)
                ->whereNotNull('phone')
                ->select('id', 'name', 'username', 'phone', 'county', 'constituency', 'ward', 'polling_station', 'created_at')
                ->latest()
                ->paginate(10, ['*'], 'call_page')
                ->withQueryString()
            : collect();
        $callLogs = $key === 'call-center'
            ? CandidateCallLog::with(['caller', 'voter'])
                ->where('candidate_id', $candidate->id)
                ->latest('called_at')
                ->take(12)
                ->get()
            : collect();

        return view('aspirants.tools.show', [
            'candidate' => $candidate,
            'module' => $module,
            'scope' => $scope,
            'isBlocked' => $isBlocked,
            'voterCount' => $voterCount,
            'recentVoters' => $recentVoters,
            'polls' => $polls,
            'websiteRequest' => $websiteRequest,
            'websiteSamples' => $websiteSamples,
            'callScript' => $callScript,
            'callListActive' => $callListActive,
            'callListContacts' => $callListContacts,
            'callLogs' => $callLogs,
        ]);
    }


    public function sendBulkSms(SendBulkSmsRequest $request): RedirectResponse
    {
        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect('/aspirant/dashboard')
                ->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $availability = $this->workspaceService->canUseTool('bulk-sms', $candidate);

        if (! $availability['available']) {
            return redirect('/aspirant/dashboard')
                ->with('warning', $availability['reason']);
        }

        $scope = $this->workspaceService->scopeForCandidate($candidate);

        if ($scope['missing']) {
            return redirect()->route('aspirant.tools.show', 'bulk-sms')
                ->with('warning', $scope['message']);
        }

        $validated = $request->validated();
        $recipientCount = $this->workspaceService->registeredVotersQuery($scope)
            ->whereNotNull('phone')
            ->count();

        $smsMessage = $this->smsMessageRepository->create([
            'candidate_id' => $candidate->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'scope_type' => $scope['type'],
            'scope_column' => $scope['column'],
            'scope_value' => $scope['value'],
            'recipient_count' => $recipientCount,
            'status' => 'queued',
        ]);

        SendCandidateBulkSms::dispatch($smsMessage->id);

        Log::info('Bulk SMS queued from aspirant workspace.', [
            'sms_message_id' => $smsMessage->id,
            'candidate_id' => $candidate->id,
            'user_id' => $request->user()->id,
            'scope_type' => $scope['type'],
            'scope_column' => $scope['column'],
            'scope_value' => $scope['value'],
            'recipient_count' => $recipientCount,
        ]);

        return redirect()->route('aspirant.tools.show', 'bulk-sms')
            ->with('success', 'Bulk SMS queued for ' . number_format($recipientCount) . ' voters in ' . $scope['label'] . '. Delivery will be confirmed after Infobip processes the job.');
    }

    public function storePoll(Request $request): RedirectResponse
    {
        if (! $this->workspaceService->publishedToolForKey('opinion-polls')) {
            return redirect('/aspirant/dashboard')
                ->with('warning', 'Opinion polls are not enabled yet. Ask an admin to publish the tool first.');
        }

        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect('/aspirant/dashboard')
                ->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $scope = $this->workspaceService->scopeForCandidate($candidate);

        if ($scope['missing']) {
            return redirect()->route('aspirant.tools.show', 'opinion-polls')
                ->with('warning', $scope['message']);
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'options' => ['required', 'string'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        $status = $validated['status'] ?? 'draft';

        $options = collect(preg_split('/\r\n|\r|\n/', $validated['options']))
            ->map(fn (string $option): string => trim($option))
            ->filter()
            ->values();

        if ($options->count() < 2) {
            return redirect()->back()
                ->withInput()
                ->with('warning', 'Add at least two poll options.');
        }

        DB::transaction(function () use ($request, $candidate, $scope, $validated, $options, $status): void {
            $group = null;

            if ($status === 'published') {
                $group = $this->scopedPollGroup($request, $scope);

                GroupMember::firstOrCreate([
                    'group_id' => $group->id,
                    'user_id' => $request->user()->id,
                ]);

                $this->workspaceService->registeredVotersQuery($scope)
                    ->select('id')
                    ->orderBy('id')
                    ->chunkById(200, function ($voters) use ($group): void {
                        foreach ($voters as $voter) {
                            GroupMember::firstOrCreate([
                                'group_id' => $group->id,
                                'user_id' => $voter->id,
                            ]);
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
        });

        $message = $status === 'published'
            ? 'Poll published to the ' . $scope['label'] . ' chat group.'
            : 'Poll draft saved. Publish it when you are ready to send it to voters.';

        return redirect()->route('aspirant.tools.show', 'opinion-polls')
            ->with('success', $message);
    }


    public function saveCallScript(Request $request): RedirectResponse
    {
        if (! $this->workspaceService->publishedToolForKey('call-center')) {
            return redirect('/aspirant/dashboard')
                ->with('warning', 'Call Center is not enabled yet. Ask an admin to publish the tool first.');
        }

        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect('/aspirant/dashboard')
                ->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $scope = $this->workspaceService->scopeForCandidate($candidate);

        if ($scope['missing']) {
            return redirect()->route('aspirant.tools.show', 'call-center')
                ->with('warning', $scope['message']);
        }

        $validated = $request->validate([
            'script' => ['required', 'string', 'min:20', 'max:5000'],
            'callback_priority' => ['required', 'in:undecided,supporters,volunteers'],
        ]);

        CandidateCallScript::updateOrCreate(
            ['candidate_id' => $candidate->id],
            [
                'user_id' => $request->user()->id,
                'script' => $validated['script'],
                'callback_priority' => $validated['callback_priority'],
                'scope_type' => $scope['type'],
                'scope_column' => $scope['column'],
                'scope_value' => $scope['value'],
            ]
        );

        return redirect()->route('aspirant.tools.show', 'call-center')
            ->with('success', 'Call script saved. You can now start the scoped call list.');
    }

    public function storeCallLog(Request $request): RedirectResponse|JsonResponse
    {
        if (! $this->workspaceService->publishedToolForKey('call-center')) {
            return $this->callLogFailure($request, 'Call Center is not enabled yet. Ask an admin to publish the tool first.', 403);
        }

        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return $this->callLogFailure($request, 'No aspirant profile is linked to this account yet.', 403);
        }

        $scope = $this->workspaceService->scopeForCandidate($candidate);

        if ($scope['missing']) {
            return $this->callLogFailure($request, $scope['message'], 422);
        }

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
            return $this->callLogFailure($request, 'That voter is not available in your scoped call list.', 404);
        }

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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Call log recorded.',
                'log' => [
                    'id' => $callLog->id,
                    'voter' => $callLog->voter_name,
                    'outcome' => str_replace('_', ' ', ucfirst($callLog->outcome)),
                    'callback_at' => $callLog->callback_at?->format('M j, H:i'),
                    'notes' => $callLog->notes,
                ],
            ], 201);
        }

        return redirect()->route('aspirant.tools.show', ['key' => 'call-center', 'call_list' => 1])
            ->with('success', 'Call log recorded.');
    }

    private function callLogFailure(Request $request, string $message, int $status): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return redirect()->route('aspirant.tools.show', ['key' => 'call-center', 'call_list' => 1])
            ->with('warning', $message);
    }
    public function storeWebsiteRequest(Request $request): RedirectResponse
    {

        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect('/aspirant/dashboard')
                ->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $validated = $request->validate([
            'candidate_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'preferred_domain' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9 ._-]+(\.[a-zA-Z]{2,})?$/'],
            'website_type' => ['required', 'in:standard,premium,custom'],
            'reference_url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        CampaignWebsiteRequest::updateOrCreate(
            ['candidate_id' => $candidate->id],
            array_merge($validated, [
                'user_id' => $request->user()->id,
                'status' => 'new',
            ])
        );

        return redirect()->route('aspirant.tools.show', 'campaign-website')
            ->with('success', 'Campaign website request submitted. An admin will review it and follow up.');
    }

    public function websiteSamples(): View
    {
        $samples = CampaignWebsiteSample::published()->ordered()->get();

        return view('aspirants.tools.website-samples', compact('samples'));
    }
    private function scopedPollGroup(Request $request, array $scope): Group
    {
        $name = $scope['label'] . ' Opinion Polls';

        $group = Group::where('name', $name)
            ->where('created_by', $request->user()->id)
            ->first();

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
        $options = collect($poll->options)
            ->map(fn (string $option, int $index): string => ($index + 1) . '. ' . $option)
            ->implode("\n");

        return "[POLL #{$poll->id}]\n{$poll->question}\n{$options}";
    }
}










