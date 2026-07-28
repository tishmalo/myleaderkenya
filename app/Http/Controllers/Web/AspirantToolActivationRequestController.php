<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CampaignToolRequest;
use App\Services\Web\AspirantWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AspirantToolActivationRequestController extends Controller
{
    public function __construct(private AspirantWorkspaceService $workspaceService) {}

    public function store(Request $request): RedirectResponse
    {
        $definitions = $this->workspaceService->toolDefinitions();

        $validated = $request->validate([
            'tool_key' => ['required', 'string', Rule::in(array_keys($definitions))],
            'tool_title' => ['required', 'string', 'max:255'],
            'campaign_tool_id' => ['nullable', 'integer', 'exists:campaign_tools,id'],
            'disabled_reason' => ['nullable', 'string', 'max:2000'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $candidate = $this->workspaceService->candidateForUser($user);

        if (! $candidate) {
            return redirect('/aspirant/dashboard')
                ->with('warning', 'Link an aspirant profile before requesting tool activation.');
        }

        $toolKey = $validated['tool_key'];
        $toolTitle = $definitions[$toolKey]['title'] ?? $validated['tool_title'];
        $disabledReason = trim((string) ($validated['disabled_reason'] ?? ''));
        $message = trim((string) ($validated['message'] ?? ''));

        CampaignToolRequest::create([
            'campaign_tool_id' => $validated['campaign_tool_id'] ?? null,
            'user_id' => $user->id,
            'candidate_id' => $candidate->id,
            'request_type' => 'activation',
            'tool_key' => $toolKey,
            'tool_title' => $toolTitle,
            'requester_name' => $candidate->name ?: $user->name,
            'email' => $candidate->email ?: $user->email,
            'phone' => $candidate->phone ?: $user->phone,
            'requested_feature' => 'Activate ' . $toolTitle,
            'use_case' => $message,
            'disabled_reason' => $disabledReason,
            'status' => 'new',
        ]);

        return redirect(route('aspirant.dashboard') . '#campaign-tools')
            ->with('success', 'Activation request sent to admin.');
    }
}

