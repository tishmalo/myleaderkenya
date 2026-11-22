<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewCampaignWebsiteRequest;
use App\Models\CampaignWebsiteRequest;
use Illuminate\Http\RedirectResponse;
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

    public function update(ReviewCampaignWebsiteRequest $request, CampaignWebsiteRequest $campaignWebsiteRequest): RedirectResponse
    {
        $campaignWebsiteRequest->update($request->validated());

        return redirect()->route('campaign-website-requests.index')
            ->with('success', 'Website request updated.');
    }
}


