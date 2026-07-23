<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignTool;
use App\Models\CampaignToolRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignToolRequestController extends Controller
{
    public function index(): View
    {
        $filters = request()->only(['status', 'campaign_tool_id', 'search']);

        $requests = CampaignToolRequest::with(['campaignTool', 'candidate.position', 'user'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['campaign_tool_id'] ?? null, fn ($query, $toolId) => $query->where('campaign_tool_id', $toolId))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('requester_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('requested_feature', 'like', "%{$search}%")
                        ->orWhere('use_case', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $campaignTools = CampaignTool::ordered()->get(['id', 'title']);

        return view('campaign-tool-requests.index', compact('requests', 'campaignTools'));
    }

    public function update(Request $request, CampaignToolRequest $campaignToolRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', CampaignToolRequest::STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $campaignToolRequest->update($validated);

        return redirect()->route('campaign-tool-requests.index')
            ->with('success', 'Campaign tool request updated.');
    }

    public function destroy(CampaignToolRequest $campaignToolRequest): RedirectResponse
    {
        $campaignToolRequest->delete();

        return redirect()->route('campaign-tool-requests.index')
            ->with('success', 'Campaign tool request deleted.');
    }
}