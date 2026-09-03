<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpamRule;
use App\Models\SpamSample;
use App\Services\Web\SpamFilterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SpamFilterController extends Controller
{
    public function __construct(private SpamFilterService $spamFilter) {}

    public function index(): View
    {
        $samples = SpamSample::query()
            ->with('campaignToolRequest:id,requester_name,email,phone')
            ->latest()
            ->paginate(20);

        $rules = SpamRule::query()
            ->orderBy('type')
            ->orderBy('value')
            ->get()
            ->groupBy('type');

        $blockedIps = collect(config('spam_filter.blocked_ips', []))
            ->merge(SpamRule::query()->where('type', 'ip')->pluck('value'))
            ->unique()
            ->values();

        $overrides = \App\Models\SpamIpOverride::query()->latest()->get();

        $spamRequestCount = \App\Models\CampaignToolRequest::query()->where('is_spam', true)->count();
        $rejectedCount = $samples->total();

        return view('spam-filter.index', compact(
            'samples',
            'rules',
            'blockedIps',
            'overrides',
            'spamRequestCount',
            'rejectedCount'
        ));
    }

    public function analyze(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:20000'],
        ]);

        $text = trim($data['text']);

        $this->spamFilter->recordSample(
            ['use_case' => $text],
            'admin_pasted',
            null,
            'admin'
        );

        $suggestions = $this->spamFilter->suggestRules($text);

        return redirect()->route('spam-filter.index')
            ->with('spam_suggestions', $suggestions)
            ->with('spam_suggestion_text', $text)
            ->with('success', 'Spam message recorded. Review the suggested patterns below.');
    }

    public function storeSample(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:20000'],
        ]);

        $this->spamFilter->recordSample(
            ['use_case' => trim($data['text'])],
            'admin_pasted',
            null,
            'admin'
        );

        return redirect()->route('spam-filter.index')->with('success', 'Spam sample added.');
    }

    public function destroySample(SpamSample $spamSample): RedirectResponse
    {
        $spamSample->delete();

        return redirect()->route('spam-filter.index')->with('success', 'Spam sample deleted.');
    }

    public function blockIp(SpamSample $spamSample): RedirectResponse
    {
        $ip = $spamSample->ip;

        if (! $ip) {
            return redirect()->route('spam-filter.index')->with('warning', 'This sample has no recorded IP address.');
        }

        SpamRule::firstOrCreate(
            ['type' => 'ip', 'value' => $ip],
            ['enabled' => true, 'source' => 'admin', 'created_by' => auth()->id()]
        );

        Cache::forget('spam_rules:ip');

        return redirect()->route('spam-filter.index')->with('success', "IP {$ip} added to the blocklist.");
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:keyword,domain,email,phone,ip,regex'],
            'value' => ['required', 'string', 'max:500'],
        ]);

        try {
            SpamRule::firstOrCreate(
                ['type' => $data['type'], 'value' => trim($data['value'])],
                ['enabled' => true, 'source' => 'admin', 'created_by' => auth()->id()]
            );
        } catch (\Throwable $e) {
            return redirect()->route('spam-filter.index')->with('warning', 'That rule already exists.');
        }

        Cache::forget('spam_rules:'.$data['type']);

        return redirect()->route('spam-filter.index')->with('success', 'Spam rule added.');
    }

    public function toggleRule(SpamRule $spamRule): RedirectResponse
    {
        $spamRule->update(['enabled' => ! $spamRule->enabled]);

        Cache::forget('spam_rules:'.$spamRule->type);

        return redirect()->route('spam-filter.index')->with('success', 'Spam rule updated.');
    }

    public function destroyRule(SpamRule $spamRule): RedirectResponse
    {
        $spamRule->delete();

        Cache::forget('spam_rules:'.$spamRule->type);

        return redirect()->route('spam-filter.index')->with('success', 'Spam rule deleted.');
    }
}