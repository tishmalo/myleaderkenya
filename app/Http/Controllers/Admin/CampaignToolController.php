<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignToolStoreRequest;
use App\Http\Requests\Admin\CampaignToolUpdateRequest;
use App\Models\CampaignTool;
use App\Services\Admin\CampaignToolService;

class CampaignToolController extends Controller
{
    public function __construct(
        private CampaignToolService $campaignToolService
    ) {}

    public function index()
    {
        $filters = request()->only(['status', 'search']);
        $campaignTools = $this->campaignToolService->getPaginatedTools($filters);

        return view('campaign-tools.index', compact('campaignTools'));
    }

    public function create()
    {
        return view('campaign-tools.create');
    }

    public function store(CampaignToolStoreRequest $request)
    {
        $this->campaignToolService->createTool(
            $request->except(['featured_image']),
            $request->file('featured_image')
        );

        return redirect()->route('campaign-tools.index')
                         ->with('success', 'Campaign tool created successfully!');
    }

    public function edit(CampaignTool $campaignTool)
    {
        return view('campaign-tools.edit', compact('campaignTool'));
    }

    public function update(CampaignToolUpdateRequest $request, CampaignTool $campaignTool)
    {
        $this->campaignToolService->updateTool(
            $campaignTool,
            $request->except(['featured_image']),
            $request->file('featured_image')
        );

        return redirect()->route('campaign-tools.index')
                         ->with('success', 'Campaign tool updated successfully!');
    }

    public function destroy(CampaignTool $campaignTool)
    {
        $this->campaignToolService->deleteTool($campaignTool);

        return response()->json([
            'success' => true,
            'message' => 'Campaign tool deleted successfully.',
        ]);
    }

    public function publicIndex()
    {
        $campaignTools = $this->campaignToolService->getPublishedTools(12);

        return view('campaign-tools.public.index', compact('campaignTools'));
    }

    public function publicShow(string $slug)
    {
        $campaignTool = $this->campaignToolService->getPublicShowData($slug);

        return view('campaign-tools.public.show', compact('campaignTool'));
    }
}