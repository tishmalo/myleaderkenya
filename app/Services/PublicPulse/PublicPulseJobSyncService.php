<?php

namespace App\Services\PublicPulse;

use App\Contracts\Repositories\Web\PublicPulseJobRepositoryInterface;
use App\Contracts\Services\PublicPulseEngineClientInterface;
use App\Models\PublicPulseJob;
use Throwable;

class PublicPulseJobSyncService
{
    public function __construct(
        private PublicPulseJobRepositoryInterface $jobs,
        private PublicPulseEngineClientInterface $engine
    ) {}

    public function syncDue(int $limit = 50): array
    {
        $synced = 0;
        $failed = 0;

        foreach ($this->jobs->dueForSync($limit) as $job) {
            $this->sync($job) ? $synced++ : $failed++;
        }

        return compact('synced', 'failed');
    }

    public function sync(PublicPulseJob $job): bool
    {
        if ($job->isTerminal() || ! $job->engine_job_id) {
            return true;
        }

        try {
            $payload = $this->engine->jobStatus($job->engine_job_id);
            $attributes = [
                'status' => $payload['status'],
                'partial' => (bool) ($payload['partial'] ?? false),
                'summary' => $payload['summary'] ?? $job->summary,
                'error_message' => $payload['error_msg'] ?? null,
                'last_synced_at' => now(),
            ];

            if (in_array($payload['status'], PublicPulseJob::TERMINAL_STATUSES, true)) {
                $attributes['completed_at'] = $payload['completed_at'] ?? now();
            }

            $this->jobs->updateNonTerminal($job, $attributes);

            return true;
        } catch (Throwable $exception) {
            report($exception);
            $this->jobs->update($job, ['last_synced_at' => now()]);

            return false;
        }
    }
}