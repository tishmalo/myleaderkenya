<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignToolStoreRequest;
use App\Http\Requests\Admin\CampaignToolUpdateRequest;
use App\Http\Requests\Web\StoreCampaignToolFeatureRequest;
use App\Models\CampaignTool;
use App\Services\Admin\CampaignToolService;
use App\Services\Admin\SettingService;
use App\Services\Web\AspirantWorkspaceService;
use App\Services\Web\CampaignToolFeatureRequestService;
use Illuminate\Http\RedirectResponse;

class CampaignToolController extends Controller
{
    public function __construct(
        private CampaignToolService $campaignToolService,
        private SettingService $settingService,
        private AspirantWorkspaceService $workspaceService,
        private CampaignToolFeatureRequestService $featureRequestService
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
        $campaignTool = $this->campaignToolService->getToolForEdit($campaignTool);

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
        $campaignToolsSeo = $this->settingService->getFrontendPage('campaign-tools');
        $requestCampaignTools = $this->campaignToolService->getRequestToolOptions();

        return view('campaign-tools.public.index', compact('campaignTools', 'campaignToolsSeo', 'requestCampaignTools'));
    }

    public function publicShow(string $slug)
    {
        $campaignTool = $this->campaignToolService->getPublicShowData($slug);
        $requestCampaignTools = $this->campaignToolService->getRequestToolOptions();

        return view('campaign-tools.public.show', compact('campaignTool', 'requestCampaignTools'));
    }

    public function storeFeatureRequest(StoreCampaignToolFeatureRequest $request, CampaignTool $campaignTool): RedirectResponse
    {
        abort_unless($campaignTool->status === 'published', 404);

        $user = $request->user();
        $candidate = $user ? $this->workspaceService->candidateForUser($user) : null;

        $this->featureRequestService->submit($campaignTool, $request->validated(), $user, $candidate);

        return redirect()->back()
                         ->with('success', 'Feature request submitted. The admin team will review it.');
    }
}
