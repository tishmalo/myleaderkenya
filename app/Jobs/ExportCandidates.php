<?php

namespace App\Jobs;

use App\Models\CandidateTransferRun;
use App\Services\Admin\CandidateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ExportCandidates implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(public int $runId) {}

    public function handle(CandidateService $service): void
    {
        $run = CandidateTransferRun::findOrFail($this->runId);
        $run->update(['status' => 'running', 'started_at' => now(), 'error_message' => null]);

        $filters = $run->filters ?? [];
        $downloadName = 'candidates-'.now()->format('Y-m-d-Hi').'.csv';

        $result = $service->exportCandidatesToCsv($filters, $downloadName);

        $run->update([
            'status' => 'complete',
            'completed_at' => now(),
            'result_path' => $result['path'],
            'download_name' => $downloadName,
            'exported_count' => $result['count'],
            'error_message' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        CandidateTransferRun::whereKey($this->runId)->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }
}