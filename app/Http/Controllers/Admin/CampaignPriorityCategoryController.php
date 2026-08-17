<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignPriorityCategoryFilterRequest;
use App\Http\Requests\Admin\SaveCampaignPriorityCategoryRequest;
use App\Models\CampaignPriorityCategory;
use App\Services\Admin\CampaignPriorityCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CampaignPriorityCategoryController extends Controller
{
    public function __construct(
        private CampaignPriorityCategoryService $service,
    ) {}

    public function index(CampaignPriorityCategoryFilterRequest $request): View
    {
        return view('campaign-priorities.index', $this->service->indexData($request->validated()));
    }

    public function store(SaveCampaignPriorityCategoryRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), (int) $request->user()->id);

        return back()->with('success', 'Campaign priority category created.');
    }

    public function update(SaveCampaignPriorityCategoryRequest $request, CampaignPriorityCategory $campaignPriorityCategory): RedirectResponse
    {
        $this->service->update(
            $campaignPriorityCategory,
            $request->validated(),
            (int) $request->user()->id,
        );

        return back()->with('success', 'Campaign priority category updated.');
    }

    public function destroy(CampaignPriorityCategory $campaignPriorityCategory): RedirectResponse
    {
        $this->service->delete($campaignPriorityCategory);

        return back()->with('success', 'Unused campaign priority category deleted.');
    }
}
