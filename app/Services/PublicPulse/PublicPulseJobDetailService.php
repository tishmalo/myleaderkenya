<?php
namespace App\Services\PublicPulse;
use App\Contracts\Services\PublicPulseEngineClientInterface;
use App\Models\PublicPulseJob;
class PublicPulseJobDetailService
{
    public function __construct(private PublicPulseEngineClientInterface $engine) {}
    public function tweets(PublicPulseJob $job, array $filters = []): ?array
    {
        if (! $job->engine_job_id) return null;
        return $this->engine->tweets($job->engine_job_id, $filters);
    }
}
