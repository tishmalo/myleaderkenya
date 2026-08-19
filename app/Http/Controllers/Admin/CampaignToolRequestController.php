<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignToolRequestUpdateRequest;
use App\Models\CampaignToolRequest;
use App\Services\Admin\CampaignToolRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CampaignToolRequestController extends Controller
{
    public function __construct(private CampaignToolRequestService $service) {}

    public function index(): View
    {
        $filters = request()->only(['status', 'request_type', 'payment_status', 'campaign_tool_id', 'search']);
        $requests = $this->service->paginate($filters);
        $campaignTools = $this->service->getToolOptions();

        return view('campaign-tool-requests.index', compact('requests', 'campaignTools'));
    }

    public function update(CampaignToolRequestUpdateRequest $request, CampaignToolRequest $campaignToolRequest): RedirectResponse
    {
        $this->service->update($campaignToolRequest, $request->user(), $request->validated());

        return redirect()->route('campaign-tool-requests.index')
            ->with('success', 'Campaign tool request updated.');
    }

    public function destroy(CampaignToolRequest $campaignToolRequest): RedirectResponse
    {
        if (! $this->service->canDelete($campaignToolRequest)) {
            return redirect()->route('campaign-tool-requests.index')
                ->with('warning', 'Adoption sponsorship records are financial records and cannot be deleted. Cancel the request to refund it.');
        }

        $this->service->delete($campaignToolRequest);

        return redirect()->route('campaign-tool-requests.index')
            ->with('success', 'Campaign tool request deleted.');
    }
}
