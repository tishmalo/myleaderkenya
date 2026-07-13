<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\Web\CandidateSmsMessageRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SendBulkSmsRequest;
use App\Models\AspirantPoll;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Services\Sms\InfobipSmsService;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AspirantToolController extends Controller
{
    public function __construct(
        private AspirantWorkspaceService $workspaceService,
        private InfobipSmsService $smsService,
        private CandidateSmsMessageRepositoryInterface $smsMessageRepository
    ) {}

    public function show(Request $request, string $key): View|RedirectResponse
    {
        $definitions = $this->workspaceService->toolDefinitions();

        if (! isset($definitions[$key])) {
            abort(404);
        }

        $tool = $this->workspaceService->publishedToolForKey($key);

        if (! $tool) {
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

        return view('aspirants.tools.show', [
            'candidate' => $candidate,
            'module' => $module,
            'scope' => $scope,
            'isBlocked' => $isBlocked,
            'voterCount' => $voterCount,
            'recentVoters' => $recentVoters,
            'polls' => $polls,
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
        $recipients = $this->workspaceService->registeredVotersQuery($scope)
            ->whereNotNull('phone')
            ->select('id', 'phone')
            ->get();

        $smsMessage = $this->smsMessageRepository->create([
            'candidate_id' => $candidate->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'scope_type' => $scope['type'],
            'scope_column' => $scope['column'],
            'scope_value' => $scope['value'],
            'recipient_count' => $recipients->count(),
            'status' => 'queued',
        ]);

        try {
            $result = $this->smsService->sendBulk($candidate->smsSetting, $recipients, $validated['message']);

            $this->smsMessageRepository->update($smsMessage, [
                'recipient_count' => $result['recipient_count'],
                'status' => $result['success'] ? 'sent' : 'failed',
                'provider_response' => $result,
                'sent_at' => $result['success'] ? now() : null,
            ]);

            if (! $result['success']) {
                return redirect()->route('aspirant.tools.show', 'bulk-sms')
                    ->with('warning', $result['message']);
            }

            return redirect()->route('aspirant.tools.show', 'bulk-sms')
                ->with('success', 'Bulk SMS sent to ' . number_format($result['recipient_count']) . ' voters in ' . $scope['label'] . '.');
        } catch (RequestException $exception) {
            $response = $exception->response?->json() ?? ['message' => $exception->getMessage()];

            $this->smsMessageRepository->update($smsMessage, [
                'status' => 'failed',
                'provider_response' => $response,
            ]);

            return redirect()->route('aspirant.tools.show', 'bulk-sms')
                ->with('warning', 'Infobip rejected the SMS request. Check the candidate sender ID, API key, and base URL.');
        } catch (Throwable $exception) {
            $this->smsMessageRepository->update($smsMessage, [
                'status' => 'failed',
                'provider_response' => ['message' => $exception->getMessage()],
            ]);

            return redirect()->route('aspirant.tools.show', 'bulk-sms')
                ->with('warning', 'Bulk SMS could not be sent right now. Please check the candidate SMS settings.');
        }
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
        ]);

        $options = collect(preg_split('/\r\n|\r|\n/', $validated['options']))
            ->map(fn (string $option): string => trim($option))
            ->filter()
            ->values();

        if ($options->count() < 2) {
            return redirect()->back()
                ->withInput()
                ->with('warning', 'Add at least two poll options.');
        }

        DB::transaction(function () use ($request, $candidate, $scope, $validated, $options): void {
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

            $poll = AspirantPoll::create([
                'candidate_id' => $candidate->id,
                'user_id' => $request->user()->id,
                'group_id' => $group->id,
                'question' => $validated['question'],
                'options' => $options->all(),
                'scope_type' => $scope['type'],
                'scope_column' => $scope['column'],
                'scope_value' => $scope['value'],
                'status' => 'published',
                'published_at' => now(),
            ]);

            GroupMessage::create([
                'group_id' => $group->id,
                'username' => $request->user()->username ?? $request->user()->name ?? 'Aspirant',
                'message' => $this->pollMessage($poll),
                'latitude' => null,
                'longitude' => null,
            ]);
        });

        return redirect()->route('aspirant.tools.show', 'opinion-polls')
            ->with('success', 'Poll published to the ' . $scope['label'] . ' chat group.');
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