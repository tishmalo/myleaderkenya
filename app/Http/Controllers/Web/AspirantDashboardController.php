<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CampaignTool;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AspirantDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $candidate = $this->candidateForUser($user);
        $campaignTools = CampaignTool::published()->ordered()->get();

        return view('aspirants.dashboard', [
            'user' => $user,
            'candidate' => $candidate,
            'campaignTools' => $campaignTools,
            'toolModules' => $this->toolModules($campaignTools),
        ]);
    }

    private function candidateForUser($user): ?Candidate
    {
        $query = Candidate::with(['position', 'politicalParty']);

        if (Schema::hasColumn('candidates', 'user_id')) {
            $candidate = (clone $query)->where('user_id', $user->id)->latest()->first();

            if ($candidate) {
                return $candidate;
            }
        }

        return $query->where(function ($candidateQuery) use ($user) {
            if (! empty($user->email)) {
                $candidateQuery->orWhere('email', $user->email);
            }

            if (! empty($user->phone)) {
                $candidateQuery->orWhere('phone', $user->phone);
            }
        })->latest()->first();
    }

    private function toolModules($campaignTools): array
    {
        return collect([
            ['key' => 'bulk-sms', 'title' => 'Bulk SMS', 'icon' => 'fa-comment-sms', 'summary' => 'Send targeted messages to voters, agents, volunteers, and supporters.'],
            ['key' => 'bulk-whatsapp', 'title' => 'Bulk WhatsApp', 'icon' => 'fa-brands fa-whatsapp', 'summary' => 'Coordinate groups, county teams, and rapid campaign updates.'],
            ['key' => 'opinion-polls', 'title' => 'Opinion Polls', 'icon' => 'fa-square-poll-vertical', 'summary' => 'Run issue polls and track voter sentiment by location.'],
            ['key' => 'campaign-website', 'title' => 'Campaign Website', 'icon' => 'fa-globe', 'summary' => 'Publish your biography, agenda, donation links, photos, and updates.'],
            ['key' => 'voter-database', 'title' => 'Voter Database', 'icon' => 'fa-database', 'summary' => 'Organize supporter lists, locations, segments, and follow-ups.'],
            ['key' => 'call-center', 'title' => 'Call Center', 'icon' => 'fa-headset', 'summary' => 'Manage outreach calls, scripts, callbacks, and canvassing feedback.'],
        ])->map(function (array $module) use ($campaignTools) {
            $match = $campaignTools->first(function (CampaignTool $tool) use ($module) {
                $haystack = strtolower($tool->title . ' ' . $tool->nav_label . ' ' . $tool->excerpt . ' ' . $tool->slug);

                return str_contains($haystack, str_replace('-', ' ', $module['key']))
                    || str_contains($haystack, $module['title'] ? strtolower($module['title']) : $module['key']);
            });

            $module['tool'] = $match;
            $module['url'] = $match ? route('campaign-tools.show', $match->slug) : route('campaign-tools.public');
            $module['available'] = (bool) $match;

            return $module;
        })->all();
    }
}