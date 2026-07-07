<?php

namespace App\Http\Controllers\Aspirant;

use App\Http\Controllers\Controller;
use App\Models\CampaignTool;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $candidate = auth()->user()->candidateProfile();
        $campaignTools = $candidate?->approval_status === 'approved'
            ? CampaignTool::published()->ordered()->get()
            : collect();

        return view('aspirants.dashboard', compact('candidate', 'campaignTools'));
    }
}
