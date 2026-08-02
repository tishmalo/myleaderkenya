<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\RemoveAspirantTeamMemberRequest;
use App\Http\Requests\Web\UpdateAspirantMediaRequest;
use App\Http\Requests\Web\UpdateAspirantSocialLinksRequest;
use App\Models\CampaignPriorityCategory;
use App\Models\CampaignTool;
use App\Models\User;
use App\Services\Admin\CandidateService;
use App\Services\Web\AspirantTeamService;
use App\Services\Web\AspirantTokenService;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AspirantDashboardController extends Controller
{
    public function __construct(
        private AspirantWorkspaceService $workspaceService,
        private AspirantTokenService $tokenService,
        private CandidateService $candidateService,
        private AspirantTeamService $teamService
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $candidate = $this->workspaceService->candidateForUser($user);
        $campaignTools = CampaignTool::published()->ordered()->get();
        $campaignPriorityCategories = CampaignPriorityCategory::query()
            ->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $campaignPriorityEntries = $candidate
            ? $candidate->campaignPriorities()->get()->keyBy('campaign_priority_category_id')
            : collect();
        $scope = $this->workspaceService->scopeForCandidate($candidate);
        $toolModules = $this->workspaceService->toolModules($campaignTools, $candidate);
        $scopeMissing = (bool) ($scope['missing'] ?? false);
        $voterQuery = $this->workspaceService->registeredVotersQuery($scope);
        $scopedVoterCount = $scopeMissing ? 0 : (clone $voterQuery)->count();
        $reachableVoterCount = $scopeMissing ? 0 : (clone $voterQuery)->whereNotNull('phone')->count();
        $tokenWallet = $candidate ? $this->tokenService->walletForCandidate($candidate) : null;
        $isPrimaryAspirant = $this->teamService->isPrimaryAspirant($user, $candidate);

        return view('aspirants.dashboard', [
            'user' => $user,
            'candidate' => $candidate,
            'campaignTools' => $campaignTools,
            'campaignPriorityCategories' => $campaignPriorityCategories,
            'campaignPriorityEntries' => $campaignPriorityEntries,
            'toolModules' => $toolModules,
            'voterScope' => $scope,
            'dashboardStats' => [
                'scoped_voters' => $scopedVoterCount,
                'reachable_voters' => $reachableVoterCount,
                'enabled_tools' => collect($toolModules)->where('available', true)->count(),
                'setup_tools' => collect($toolModules)->where('available', false)->count(),
                'active_polls' => $this->activePollCount($candidate?->id),
            ],
            'recentOutreach' => $this->recentOutreach($candidate?->id),
            'pollSnapshot' => $this->pollSnapshot($candidate?->id),
            'tokenWallet' => $tokenWallet,
            'isPrimaryAspirant' => $isPrimaryAspirant,
            'teamMembers' => $isPrimaryAspirant ? $this->teamService->teamForOwner($user, $candidate) : collect(),
        ]);
    }

    public function updateCoverPhoto(Request $request): RedirectResponse
    {
        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect(route('aspirant.dashboard') . '#profile')
                ->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $request->validate([
            'cover_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $this->candidateService->updateCandidate(
            $candidate,
            [],
            null,
            $request->file('cover_photo')
        );

        return redirect(route('aspirant.dashboard') . '#profile')
            ->with('success', 'Cover photo updated successfully.');
    }

    public function updateSocialLinks(UpdateAspirantSocialLinksRequest $request): RedirectResponse
    {
        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect(route('aspirant.dashboard') . '#profile')
                ->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $this->candidateService->updateCandidate($candidate, $request->validated());

        return redirect(route('aspirant.dashboard') . '#profile')
            ->with('success', 'Social media links updated successfully.');
    }

    public function updateMedia(UpdateAspirantMediaRequest $request): RedirectResponse
    {
        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect(route('aspirant.dashboard') . '#profile')
                ->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $this->candidateService->updateCandidate(
            $candidate,
            $request->safe()->except(['campaign_poster', 'campaign_skiza_audio']),
            null,
            null,
            $request->file('campaign_poster'),
            null,
            $request->file('campaign_skiza_audio')
        );

        return redirect(route('aspirant.dashboard') . '#profile')
            ->with('success', 'Campaign media updated successfully.');
    }
    public function removeTeamMember(RemoveAspirantTeamMemberRequest $request, User $member): RedirectResponse
    {
        $candidate = $this->workspaceService->candidateForUser($request->user());

        if (! $candidate) {
            return redirect(route('aspirant.dashboard') . '#team')
                ->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        if (! $this->teamService->removeMember($request->user(), $candidate, $member)) {
            return redirect(route('aspirant.dashboard') . '#team')
                ->with('warning', 'Only the primary aspirant can remove campaign team members.');
        }

        return redirect(route('aspirant.dashboard') . '#team')
            ->with('success', 'Campaign team member removed. Their dashboard access has been revoked.');
    }

    private function activePollCount(?int $candidateId): int
    {
        if (! $candidateId || ! Schema::hasTable('aspirant_polls')) {
            return 0;
        }

        return DB::table('aspirant_polls')
            ->where('candidate_id', $candidateId)
            ->whereIn('status', ['published', 'active'])
            ->count();
    }

    private function recentOutreach(?int $candidateId): array
    {
        if (! $candidateId || ! Schema::hasTable('candidate_sms_messages')) {
            return [];
        }

        return DB::table('candidate_sms_messages')
            ->where('candidate_id', $candidateId)
            ->latest('created_at')
            ->limit(4)
            ->get(['status', 'recipient_count', 'scope_value', 'created_at'])
            ->map(fn ($message): array => [
                'channel' => 'Bulk SMS',
                'audience' => $message->scope_value ?: 'Voting bloc',
                'status' => ucfirst((string) $message->status),
                'recipients' => (int) $message->recipient_count,
                'last_sent' => $message->created_at ? date('M j, H:i', strtotime($message->created_at)) : '-',
            ])
            ->all();
    }

    private function pollSnapshot(?int $candidateId): ?array
    {
        if (! $candidateId || ! Schema::hasTable('aspirant_polls') || ! Schema::hasTable('aspirant_poll_responses')) {
            return null;
        }

        $poll = DB::table('aspirant_polls')
            ->where('candidate_id', $candidateId)
            ->whereIn('status', ['published', 'active'])
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->first(['id', 'question', 'options']);

        if (! $poll) {
            return null;
        }

        $options = is_string($poll->options) ? json_decode($poll->options, true) : (array) $poll->options;
        $options = collect($options)->filter(fn ($option): bool => is_string($option) && trim($option) !== '')->values();

        if ($options->isEmpty()) {
            return null;
        }

        $counts = DB::table('aspirant_poll_responses')
            ->where('aspirant_poll_id', $poll->id)
            ->select('option_index', DB::raw('COUNT(*) as response_count'))
            ->groupBy('option_index')
            ->pluck('response_count', 'option_index');
        $total = (int) $counts->sum();

        return [
            'question' => $poll->question,
            'total' => $total,
            'options' => $options->map(function (string $label, int $index) use ($counts, $total): array {
                $count = (int) ($counts[$index] ?? 0);

                return [
                    'label' => $label,
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100) : 0,
                ];
            })->all(),
        ];
    }
}
