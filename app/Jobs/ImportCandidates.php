<?php

namespace App\Jobs;

use App\Models\CandidateTransferRun;
use App\Services\Admin\CandidateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportCandidates implements ShouldQueue
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

        $disk = Storage::disk('local');
        $path = $run->source_path;

        if (! $path || ! $disk->exists($path)) {
            throw new \RuntimeException('The uploaded CSV file could not be found on disk.');
        }

        try {
            $result = $service->importCandidatesFromCsv($disk->path($path));

            $run->update([
                'status' => 'complete',
                'completed_at' => now(),
                'imported_count' => $result['imported'],
                'linked_count' => $result['linked'],
                'skipped_count' => count($result['errors']),
                'errors' => array_slice($result['errors'], 0, 100),
                'error_message' => null,
            ]);
        } finally {
            $disk->delete($path);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $run = CandidateTransferRun::find($this->runId);

        if (! $run) {
            return;
        }

        $run->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ]);

        if ($run->source_path) {
            Storage::disk('local')->delete($run->source_path);
        }
    }
}