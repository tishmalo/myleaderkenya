<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignToolRequestUpdateRequest;
use App\Services\Web\DonorToolboxService;
use App\Models\CampaignTool;
use App\Models\CampaignToolRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CampaignToolRequestController extends Controller
{
    public function __construct(private DonorToolboxService $toolbox) {}

    public function index(): View
    {
        $filters = request()->only(['status', 'request_type', 'payment_status', 'campaign_tool_id', 'search']);

        $requests = CampaignToolRequest::with(['campaignTool', 'selectedTools:id,title,sponsorship_token_cost', 'candidate.position', 'user'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['request_type'] ?? null, fn ($query, $type) => $query->where('request_type', $type))
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status))
            ->when($filters['campaign_tool_id'] ?? null, function ($query, $toolId): void {
                $query->where(function ($toolQuery) use ($toolId): void {
                    $toolQuery->where('campaign_tool_id', $toolId)
                        ->orWhereHas('selectedTools', fn ($selected) => $selected->where('campaign_tools.id', $toolId));
                });
            })
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('requester_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('requested_feature', 'like', "%{$search}%")
                        ->orWhere('tool_key', 'like', "%{$search}%")
                        ->orWhere('tool_title', 'like', "%{$search}%")
                        ->orWhere('disabled_reason', 'like', "%{$search}%")
                        ->orWhere('use_case', 'like', "%{$search}%")
                        ->orWhereHas('selectedTools', fn ($toolQuery) => $toolQuery->where('title', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $campaignTools = CampaignTool::ordered()->get(['id', 'title']);

        return view('campaign-tool-requests.index', compact('requests', 'campaignTools'));
    }

    public function update(CampaignToolRequestUpdateRequest $request, CampaignToolRequest $campaignToolRequest): RedirectResponse
    {
        $validated = $request->validated();

        if ($campaignToolRequest->request_type === 'adoption'
            && $validated['status'] === 'cancelled'
            && $campaignToolRequest->payment_status === 'paid') {
            $this->toolbox->refundAdoption($campaignToolRequest, 'Sponsorship cancelled by an administrator.');
        }

        $campaignToolRequest->update($validated);

        return redirect()->route('campaign-tool-requests.index')
            ->with('success', 'Campaign tool request updated.');
    }

    public function destroy(CampaignToolRequest $campaignToolRequest): RedirectResponse
    {
        if ($campaignToolRequest->request_type === 'adoption') {
            return redirect()->route('campaign-tool-requests.index')
                ->with('warning', 'Adoption sponsorship records are financial records and cannot be deleted. Cancel the request to refund it.');
        }

        $campaignToolRequest->delete();

        return redirect()->route('campaign-tool-requests.index')
            ->with('success', 'Campaign tool request deleted.');
    }
}

