<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignToolStoreRequest;
use App\Http\Requests\Admin\CampaignToolUpdateRequest;
use App\Models\CampaignTool;
use App\Models\CampaignToolRequest;
use App\Services\Admin\CampaignToolService;
use App\Services\Admin\SettingService;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CampaignToolController extends Controller
{
    public function __construct(
        private CampaignToolService $campaignToolService,
        private SettingService $settingService
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
        $campaignToolsSeo = $this->settingService->getFrontendPage('campaign-tools');
        $requestCampaignTools = CampaignTool::published()->ordered()->get(['id', 'title']);

        return view('campaign-tools.public.index', compact('campaignTools', 'campaignToolsSeo', 'requestCampaignTools'));
    }

    public function publicShow(string $slug)
    {
        $campaignTool = $this->campaignToolService->getPublicShowData($slug);
        $requestCampaignTools = CampaignTool::published()->ordered()->get(['id', 'title']);

        return view('campaign-tools.public.show', compact('campaignTool', 'requestCampaignTools'));
    }
    public function storeFeatureRequest(Request $request, CampaignTool $campaignTool, AspirantWorkspaceService $workspaceService): RedirectResponse
    {
        abort_unless($campaignTool->status === 'published', 404);

        $validated = $request->validate([
            'requester_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'requested_feature' => ['required', 'string', 'max:255'],
            'use_case' => ['nullable', 'string', 'max:2000'],
            'feature_request_tool_id' => ['nullable', 'integer'],
            'other_campaign_tool_ids' => ['nullable', 'array'],
            'other_campaign_tool_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('campaign_tools', 'id')->where('status', 'published'),
            ],
        ]);

        if (blank($validated['email'] ?? null) && blank($validated['phone'] ?? null)) {
            throw ValidationException::withMessages([
                'phone' => 'Enter an email or phone so the team can follow up.',
            ]);
        }

        $selectedToolIds = collect($validated['other_campaign_tool_ids'] ?? [])
            ->map(fn ($toolId) => (int) $toolId)
            ->reject(fn (int $toolId) => $toolId === $campaignTool->id)
            ->unique()
            ->values()
            ->all();

        unset($validated['feature_request_tool_id'], $validated['other_campaign_tool_ids']);

        $user = $request->user();
        $candidate = $user ? $workspaceService->candidateForUser($user) : null;

        DB::transaction(function () use ($validated, $campaignTool, $user, $candidate, $selectedToolIds): void {
            $featureRequest = CampaignToolRequest::create($validated + [
                'campaign_tool_id' => $campaignTool->id,
                'user_id' => $user?->id,
                'candidate_id' => $candidate?->id,
                'request_type' => 'feature',
                'tool_title' => $campaignTool->title,
                'status' => 'new',
            ]);

            $featureRequest->selectedTools()->sync($selectedToolIds);
        });

        return redirect()->back()
            ->with('success', 'Feature request submitted. The admin team will review it.');
    }
}

