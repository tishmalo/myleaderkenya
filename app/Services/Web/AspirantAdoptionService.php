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

    public function create(Candidate $candidate, User $adopter, array $toolIds): CampaignToolRequest
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

        return DB::transaction(function () use ($candidate, $adopter, $toolIds, $tools): CampaignToolRequest {
            $primaryTool = $tools->first();
            $request = $this->toolRequests->create([
                'campaign_tool_id' => $primaryTool->id,
                'user_id' => $adopter->id,
                'candidate_id' => $candidate->id,
                'request_type' => 'adoption',
                'tool_title' => $primaryTool->title,
                'requester_name' => $adopter->name,
                'email' => $adopter->email,
                'phone' => $adopter->phone,
                'requested_feature' => 'Sponsor selected campaign tools',
                'use_case' => 'Adoption sponsorship for '.$candidate->name.'.',
                'status' => 'new',
            ]);

            $this->toolRequests->syncSelectedTools($request, $toolIds);

            return $request;
        });
    }
}