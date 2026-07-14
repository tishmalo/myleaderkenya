<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignWebsiteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignWebsiteRequestController extends Controller
{
    public function index()
    {
        $requests = CampaignWebsiteRequest::with(['candidate.position', 'user'])
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20);

        return view('campaign-websites.requests.index', compact('requests'));
    }

    public function update(Request $request, CampaignWebsiteRequest $campaignWebsiteRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,in_progress,completed,cancelled'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $campaignWebsiteRequest->update($validated);

        return redirect()->route('campaign-website-requests.index')
            ->with('success', 'Website request updated.');
    }
}


