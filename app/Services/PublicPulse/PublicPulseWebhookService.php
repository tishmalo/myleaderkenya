<?php

namespace App\Services\PublicPulse;

use App\Contracts\Repositories\Web\PublicPulseJobRepositoryInterface;
use App\Models\PublicPulseJob;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use App\Support\HomepageCache;

class PublicPulseWebhookService
{
    private const TRANSITIONS = [
        'submitting' => ['queued', 'queued_pending_capacity', 'running', 'failed'],
        'submission_failed' => ['queued', 'queued_pending_capacity', 'running', 'failed'],
        'queued_pending_capacity' => ['queued', 'running', 'failed'],
        'queued' => ['running', 'failed'],
        'running' => ['degraded', 'completed', 'failed'],
        'degraded' => ['completed', 'failed'],
    ];

    public function __construct(private PublicPulseJobRepositoryInterface $jobs) {}

    public function handle(string $rawBody, ?string $signature, array $payload): PublicPulseJob
    {
        $expected = hash_hmac('sha256', $rawBody, (string) config('services.pulse_engine.webhook_secret'));

        if (! is_string($signature) || ! hash_equals($expected, $signature)) {
            throw new AuthenticationException('Invalid Pulse Engine signature.');
        }

        $job = $this->jobs->findByJobRef((string) $payload['job_ref']);

        if (! $job) {
            throw ValidationException::withMessages(['job_ref' => 'Unknown Pulse Engine job reference.']);
        }

        if ($job->isTerminal()) {
            return $job;
        }

        $next = (string) $payload['status'];
        $allowed = self::TRANSITIONS[$job->status] ?? [];

        if ($next !== $job->status && ! in_array($next, $allowed, true)) {
            throw ValidationException::withMessages(['status' => "Invalid transition from {$job->status} to {$next}."]);
        }

        $attributes = [
            'engine_job_id' => $payload['job_id'],
            'status' => $next,
            'partial' => (bool) ($payload['partial'] ?? false),
            'summary' => $payload['summary'] ?? null,
            'error_message' => $payload['error_msg'] ?? null,
            'last_synced_at' => now(),
        ];

        if (in_array($next, PublicPulseJob::TERMINAL_STATUSES, true)) {
            $attributes['completed_at'] = now();
        }

        $this->jobs->updateNonTerminal($job, $attributes);

        if ($next === PublicPulseJob::STATUS_COMPLETED) {
            HomepageCache::flush();
        }

        return $job->refresh();
    }
}
