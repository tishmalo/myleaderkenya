<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CampaignPriorityCategory;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CandidateCampaignPriorityController extends Controller
{
    public function __construct(private AspirantWorkspaceService $workspaceService) {}

    public function update(Request $request): RedirectResponse
    {
        $candidate = $this->workspaceService->candidateForUser($request->user());
        if (! $candidate) {
            return redirect(route('aspirant.dashboard').'#campaign-priorities')->with('warning', 'No aspirant profile is linked to this account yet.');
        }

        $data = $request->validate([
            'priorities' => ['nullable', 'array'],
            'priorities.*.manifesto' => ['nullable', 'string', 'max:5000'],
        ]);
        $submitted = $data['priorities'] ?? [];
        $categories = CampaignPriorityCategory::query()->where('is_active', true)->orderBy('sort_order')->get();

        DB::transaction(function () use ($categories, $submitted, $candidate, $request): void {
            foreach ($categories as $category) {
                $manifesto = trim((string) data_get($submitted, $category->id.'.manifesto', ''));
                $existing = $candidate->campaignPriorities()->where('campaign_priority_category_id', $category->id)->first();
                if ($manifesto === '') {
                    $existing?->delete();
                    continue;
                }
                if ($existing && $existing->status !== 'rejected' && hash_equals($existing->manifesto, $manifesto)) {
                    continue;
                }
                $candidate->campaignPriorities()->updateOrCreate(
                    ['campaign_priority_category_id' => $category->id],
                    ['manifesto' => $manifesto, 'status' => 'pending', 'sort_order' => $category->sort_order, 'submitted_by' => $request->user()->id, 'reviewed_by' => null, 'reviewed_at' => null]
                );
            }
        });

        return redirect(route('aspirant.dashboard').'#campaign-priorities')->with('success', 'Campaign priorities saved and sent for admin review.');
    }
}