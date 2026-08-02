<?php

namespace App\Jobs;

use App\Services\Web\PublicApprovalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class RefreshPublicApprovalScores implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 900;

    public function handle(PublicApprovalService $publicApprovalService): void
    {
        try {
            $result = $publicApprovalService->refreshPresidentialScores();

            Log::info('Public approval scores refreshed.', $result);
        } catch (Throwable $exception) {
            Log::error('Public approval score refresh failed.', [
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}