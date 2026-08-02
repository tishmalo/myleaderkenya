<?php

namespace App\Services\PublicPulse;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Contracts\Repositories\Web\PublicPulseJobRepositoryInterface;
use App\Contracts\Services\PublicPulseEngineClientInterface;
use App\Models\PublicPulseJob;
use Illuminate\Support\Str;
use Throwable;

class PublicPulseJobSubmissionService
{
    public function __construct(
        private PublicPulseJobRepositoryInterface $jobs,
        private CandidateRepositoryInterface $candidates,
        private PublicPulseEngineClientInterface $engine
    ) {}

    public function submit(array $data, ?int $submittedBy): PublicPulseJob
    {
        $candidate = $this->candidates->find((int) $data['candidate_id']);

        if (! $candidate) {
            throw new \InvalidArgumentException('Candidate not found.');
        }

        $keywords = collect($data['keywords'] ?? [])
            ->map(fn ($keyword) => trim((string) $keyword))
            ->filter()
            ->push($candidate->name)
            ->when($candidate->nick_name, fn ($items) => $items->push($candidate->nick_name))
            ->unique(fn ($keyword) => mb_strtolower($keyword))
            ->values()
            ->all();

        $job = $this->jobs->create([
            'job_ref' => (string) Str::uuid(),
            'candidate_id' => $candidate->id,
            'submitted_by' => $submittedBy,
            'keywords' => $keywords,
            'sources' => array_values(array_unique($data['sources'] ?? ['x'])),
            'date_from' => $data['date_from'],
            'date_to' => $data['date_to'],
            'requested_limit' => (int) $data['limit'],
            'status' => PublicPulseJob::STATUS_SUBMITTING,
        ]);

        return $this->dispatch($job, $candidate->name);
    }

    public function retry(PublicPulseJob $job): PublicPulseJob
    {
        if ($job->engine_job_id || $job->isTerminal()) {
            return $job;
        }

        $candidate = $this->candidates->find((int) $job->candidate_id);
        if (! $candidate) {
            throw new \InvalidArgumentException('Candidate not found.');
        }

        return $this->dispatch($job, $candidate->name);
    }

    private function dispatch(PublicPulseJob $job, string $candidateName): PublicPulseJob
    {
        try {
            $response = $this->engine->submitJob([
                'candidate' => $candidateName,
                'keywords' => $job->keywords,
                'sources' => $job->sources ?: ['x'],
                'date_from' => $job->date_from->toDateString(),
                'date_to' => $job->date_to->toDateString(),
                'limit' => $job->requested_limit,
                'callback_url' => route('api.pulse.webhook'),
                'job_ref' => $job->job_ref,
            ]);

            return $this->jobs->update($job, [
                'engine_job_id' => $response['job_id'],
                'status' => $response['status'],
                'submitted_at' => now(),
                'last_synced_at' => now(),
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return $this->jobs->update($job, [
                'status' => 'submission_failed',
                'error_message' => str($exception->getMessage())->limit(1000),
                'last_synced_at' => now(),
            ]);
        }
    }
}
