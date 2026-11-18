<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Admin\CampaignPriorityCategoryRepositoryInterface;
use App\Models\Candidate;
use App\Models\CampaignPriorityCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CandidateCampaignPriorityService
{
    public function __construct(private CampaignPriorityCategoryRepositoryInterface $categories) {}

    /**
     * @param  array<int|string, array<string, mixed>>  $priorities
     */
    public function save(Candidate $candidate, array $priorities, User $user): void
    {
        $categories = $this->categories->active();

        DB::transaction(function () use ($categories, $priorities, $candidate, $user): void {
            foreach ($categories as $category) {
                $this->syncPriority($candidate, $category, $priorities, $user);
            }
        });
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $priorities
     */
    private function syncPriority(Candidate $candidate, CampaignPriorityCategory $category, array $priorities, User $user): void
    {
        $manifesto = trim((string) data_get($priorities, $category->id . '.manifesto', ''));
        $existing = $candidate->campaignPriorities()
            ->where('campaign_priority_category_id', $category->id)
            ->first();

        if ($manifesto === '') {
            $existing?->delete();

            return;
        }

        if ($existing && $existing->status !== 'rejected' && hash_equals((string) $existing->manifesto, $manifesto)) {
            return;
        }

        $candidate->campaignPriorities()->updateOrCreate(
            ['campaign_priority_category_id' => $category->id],
            [
                'manifesto' => $manifesto,
                'status' => 'pending',
                'sort_order' => $category->sort_order,
                'submitted_by' => $user->id,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );
    }
}
