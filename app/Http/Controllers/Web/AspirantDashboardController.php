<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CampaignTool;
use App\Services\Web\AspirantTokenService;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AspirantDashboardController extends Controller
{
    public function __construct(private AspirantWorkspaceService $workspaceService, private AspirantTokenService $tokenService) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $candidate = $this->workspaceService->candidateForUser($user);
        $campaignTools = CampaignTool::published()->ordered()->get();
        $scope = $this->workspaceService->scopeForCandidate($candidate);
        $toolModules = $this->workspaceService->toolModules($campaignTools, $candidate);
        $scopeMissing = (bool) ($scope['missing'] ?? false);
        $voterQuery = $this->workspaceService->registeredVotersQuery($scope);
        $scopedVoterCount = $scopeMissing ? 0 : (clone $voterQuery)->count();
        $reachableVoterCount = $scopeMissing ? 0 : (clone $voterQuery)->whereNotNull('phone')->count();
        $tokenWallet = $candidate ? $this->tokenService->walletForCandidate($candidate) : null;

        return view('aspirants.dashboard', [
            'user' => $user,
            'candidate' => $candidate,
            'campaignTools' => $campaignTools,
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
        ]);
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
