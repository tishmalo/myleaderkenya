<?php

namespace App\Console\Commands;

use App\Models\CampaignToolRequest;
use App\Models\SpamIpOverride;
use App\Models\SpamSample;
use Illuminate\Console\Command;

class PurgeCampaignSpam extends Command
{
    protected $signature = 'campaign-tools:purge-spam';

    protected $description = 'Delete spam campaign tool requests, spam samples, and expired IP overrides older than the retention period.';

    public function handle(): int
    {
        $retentionDays = max(1, (int) config('spam_filter.retention_days', 30));
        $cutoff = now()->subDays($retentionDays);

        $requests = CampaignToolRequest::query()
            ->where('is_spam', true)
            ->where('created_at', '<', $cutoff)
            ->delete();

        $samples = SpamSample::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $overrides = SpamIpOverride::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Purged {$requests} spam request(s), {$samples} sample(s), {$overrides} expired override(s).");

        return self::SUCCESS;
    }
}