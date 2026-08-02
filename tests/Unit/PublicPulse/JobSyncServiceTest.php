<?php
namespace Tests\Unit\PublicPulse;
use App\Contracts\Repositories\Web\PublicPulseJobRepositoryInterface;
use App\Contracts\Services\PublicPulseEngineClientInterface;
use App\Models\PublicPulseJob;
use App\Services\PublicPulse\PublicPulseJobSyncService;
use RuntimeException;
use Tests\TestCase;
class JobSyncServiceTest extends TestCase
{
    public function test_timeout_does_not_mark_active_job_failed(): void
    {
        $job=new PublicPulseJob(['engine_job_id'=>'engine-1','status'=>PublicPulseJob::STATUS_RUNNING]); $job->id=4;
        $repo=$this->createMock(PublicPulseJobRepositoryInterface::class);
        $engine=$this->createMock(PublicPulseEngineClientInterface::class);
        $engine->method('jobStatus')->willThrowException(new RuntimeException('timeout'));
        $repo->expects($this->once())->method('update')->with($job,$this->callback(fn($data)=>array_keys($data)===['last_synced_at']))->willReturn($job);
        $repo->expects($this->never())->method('updateNonTerminal');
        $this->assertFalse((new PublicPulseJobSyncService($repo,$engine))->sync($job));
        $this->assertSame(PublicPulseJob::STATUS_RUNNING,$job->status);
    }
}
