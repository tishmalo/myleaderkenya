<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignToolStoreRequest;
use App\Http\Requests\Admin\CampaignToolUpdateRequest;
use App\Models\CampaignTool;
use App\Models\CampaignToolRequest;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        ]);

        if (blank($validated['email'] ?? null) && blank($validated['phone'] ?? null)) {
            throw ValidationException::withMessages([
                'phone' => 'Enter an email or phone so the team can follow up.',
            ]);
        }

        unset($validated['feature_request_tool_id']);

        $user = $request->user();
        $candidate = $user ? $workspaceService->candidateForUser($user) : null;

        CampaignToolRequest::create($validated + [
            'campaign_tool_id' => $campaignTool->id,
            'user_id' => $user?->id,
            'candidate_id' => $candidate?->id,
            'status' => 'new',
        ]);

        return redirect()->back()
            ->with('success', 'Feature request submitted. The admin team will review it.');
    }
}
