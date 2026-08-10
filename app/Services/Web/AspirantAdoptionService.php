<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Admin\CampaignToolRepositoryInterface;
use App\Contracts\Repositories\Web\CampaignToolRequestRepositoryInterface;
use App\Models\CampaignToolRequest;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AspirantAdoptionService
{
    public function __construct(
        private CampaignToolRepositoryInterface $campaignTools,
        private CampaignToolRequestRepositoryInterface $toolRequests
    ) {}

    public function create(Candidate $candidate, User $adopter, array $toolIds, array $packageIds = []): CampaignToolRequest
    {
        $toolIds = collect($toolIds)->map(fn ($id) => (int) $id)->unique()->values()->all();
        $tools = $this->campaignTools->publishedByIds($toolIds);

        if ($tools->count() !== count($toolIds)) {
            throw ValidationException::withMessages([
                'adoption_tool_ids' => 'One or more selected campaign tools are no longer available.',
            ]);
        }

        $duplicates = $this->toolRequests->activeAdoptedToolIds($adopter->id, $candidate->id, $toolIds);

        if ($duplicates !== []) {
            $names = $tools->whereIn('id', $duplicates)->pluck('title')->implode(', ');
            throw ValidationException::withMessages([
                'adoption_tool_ids' => 'You already have an active sponsorship for: '.$names.'.',
            ]);
        }

        return DB::transaction(function () use ($candidate, $adopter, $tools, $packageIds): CampaignToolRequest {
            $requests = $tools->map(function ($tool) use ($candidate, $adopter, $packageIds) {
                $isSms = str_contains(strtolower($tool->slug.' '.$tool->title), 'bulk-sms') || str_contains(strtolower($tool->title), 'bulk sms');
                return $this->toolRequests->create([
                    'campaign_tool_id'=>$tool->id,
                    'campaign_tool_package_id'=>$isSms ? null : (int) ($packageIds[$tool->id] ?? 0),
                    'user_id'=>$adopter->id, 'candidate_id'=>$candidate->id, 'request_type'=>'adoption',
                    'fulfilment_type'=>$isSms ? 'sms_sponsorship' : 'paid_package',
                    'tool_key'=>$tool->slug, 'tool_title'=>$tool->title, 'requester_name'=>$adopter->name,
                    'email'=>$adopter->email, 'phone'=>$adopter->phone,
                    'requested_feature'=>$isSms ? 'Sponsor Bulk SMS tokens' : 'Fund and activate '.$tool->title,
                    'use_case'=>'Adoption sponsorship for '.$candidate->name.'.', 'status'=>'new',
                    'tokens_required'=>0, 'payment_status'=>$isSms ? 'awaiting_payment' : 'not_required',
                ]);
            });
            return $requests->first();
        });
    }
}
