<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOutreachCampaignRequest;
use App\Jobs\SendOutreachCampaign;
use App\Models\OutreachCampaign;
use App\Models\OutreachRecipient;
use App\Services\Admin\OutreachAudienceResolver;
use App\Services\Admin\OutreachService;
use App\Services\Sms\PlatformSmsSender;
use App\Support\MessageTemplate;
use App\Support\OutreachAudience;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The two Communications screens: compose and send an email or an SMS to a
 * chosen audience, then read the delivery report for each recipient.
 */
class OutreachController extends Controller
{
    public function __construct(
        private OutreachService $outreach,
        private OutreachAudienceResolver $audienceResolver,
        private PlatformSmsSender $sms,
    ) {}

    public function emailIndex(Request $request): View
    {
        return $this->compose($request, OutreachCampaign::CHANNEL_EMAIL);
    }

    public function smsIndex(Request $request): View
    {
        return $this->compose($request, OutreachCampaign::CHANNEL_SMS);
    }

    public function store(StoreOutreachCampaignRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $campaign = $this->outreach->create($validated['channel'], $validated, $request->user());

        SendOutreachCampaign::dispatch($campaign->getKey());

        return redirect()
            ->route('outreach.show', $campaign)
            ->with('success', $campaign->channelLabel().' message queued for '.number_format($campaign->total_recipients).' '.str('recipient')->plural($campaign->total_recipients).'.');
    }

    public function show(Request $request, OutreachCampaign $campaign): View
    {
        $status = (string) $request->query('status', '');

        $recipients = $campaign->recipients()
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.outreach.show', [
            'campaign' => $campaign,
            'recipients' => $recipients,
            'status' => $status,
            'statuses' => [
                OutreachRecipient::STATUS_PENDING => 'Pending',
                OutreachRecipient::STATUS_SENT => 'Sent',
                OutreachRecipient::STATUS_FAILED => 'Failed',
                OutreachRecipient::STATUS_SKIPPED => 'Skipped',
            ],
        ]);
    }

    private function compose(Request $request, string $channel): View
    {
        $audienceCounts = [];

        foreach (OutreachAudience::keys() as $key) {
            $audienceCounts[$key] = $this->outreach->recipientsFor($channel, $key)->count();
        }

        return view('admin.outreach.index', [
            'channel' => $channel,
            'channelLabel' => $channel === OutreachCampaign::CHANNEL_EMAIL ? 'Email' : 'SMS',
            'audiences' => OutreachAudience::all(),
            'audienceCounts' => $audienceCounts,
            'variables' => MessageTemplate::variables(),
            'previewSample' => MessageTemplate::previewSample(),
            'campaigns' => OutreachCampaign::query()
                ->forChannel($channel)
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'canSend' => (bool) $request->user()?->canAccess('messages.create'),
            'smsConfigured' => $this->sms->isConfigured(),
        ]);
    }
}
