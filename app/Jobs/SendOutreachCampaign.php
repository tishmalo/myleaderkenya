<?php

namespace App\Jobs;

use App\Models\OutreachCampaign;
use App\Services\Admin\OutreachService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Delivers one outreach campaign. Each recipient is recorded on its own row, so
 * the report shows who received the message and who did not.
 */
class SendOutreachCampaign implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 3600;

    public function __construct(private int $campaignId) {}

    public function handle(OutreachService $outreach): void
    {
        $campaign = OutreachCampaign::find($this->campaignId);

        if ($campaign === null) {
            Log::warning('Outreach campaign job skipped because the campaign no longer exists.', [
                'campaign_id' => $this->campaignId,
            ]);

            return;
        }

        $outreach->send($campaign);
    }
}
