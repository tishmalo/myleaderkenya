<?php

namespace App\Jobs;

use App\Contracts\Repositories\Web\PublicPulseSourceAccountRepositoryInterface;
use App\Models\PublicPulseSourceAccount;
use App\Notifications\PublicPulseSourceAccountIssueNotification;
use App\Services\PublicPulse\XSessionHealthCheckService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class CheckPublicPulseSourceAccountHealth implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;

    public function handle(
        PublicPulseSourceAccountRepositoryInterface $accounts,
        XSessionHealthCheckService $healthCheck
    ): void {
        $accounts->accountsDueForHealthCheck()->each(function (PublicPulseSourceAccount $account) use ($accounts, $healthCheck): void {
            $updated = $accounts->recordHealth($account, $healthCheck->check($account));

            if (! $updated->needsNotification()) {
                return;
            }

            foreach (config('auth.admin_emails', []) as $email) {
                Notification::route('mail', $email)
                    ->notify(new PublicPulseSourceAccountIssueNotification($updated));
            }

            $accounts->markIssueNotified($updated);
        });
    }
}
