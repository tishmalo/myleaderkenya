<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignWebsiteSample;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignWebsiteSampleController extends Controller
{
    public function index()
    {
        $samples = CampaignWebsiteSample::ordered()->get();

        return view('campaign-websites.samples.index', compact('samples'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'status' => ['required', 'in:draft,published'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        CampaignWebsiteSample::create($validated);

        return redirect()->route('campaign-website-samples.index')
            ->with('success', 'Website sample added.');
    }

    public function destroy(CampaignWebsiteSample $campaignWebsiteSample): RedirectResponse
    {
        $campaignWebsiteSample->delete();

        return redirect()->route('campaign-website-samples.index')
            ->with('success', 'Website sample removed.');
    }
}

