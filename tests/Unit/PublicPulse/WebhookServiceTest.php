<?php
namespace Tests\Unit\PublicPulse;
use App\Contracts\Repositories\Web\PublicPulseJobRepositoryInterface;
use App\Models\PublicPulseJob;
use App\Services\PublicPulse\PublicPulseWebhookService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
class WebhookServiceTest extends TestCase
{
    public function test_signature_is_calculated_from_exact_raw_body(): void
    {
        config(['services.pulse_engine.webhook_secret'=>'webhook-secret']);
        $repo=$this->createMock(PublicPulseJobRepositoryInterface::class);
        $service=new PublicPulseWebhookService($repo);
        $raw='{"job_ref":"11111111-1111-4111-8111-111111111111","status":"completed"}';
        $this->expectException(AuthenticationException::class);
        $service->handle($raw,hash_hmac('sha256',$raw.' ', 'webhook-secret'),['job_ref'=>'11111111-1111-4111-8111-111111111111','status'=>'completed']);
    }
    public function test_valid_signature_rejects_unknown_job(): void
    {
        config(['services.pulse_engine.webhook_secret'=>'webhook-secret']);
        $repo=$this->createMock(PublicPulseJobRepositoryInterface::class);
        $repo->method('findByJobRef')->willReturn(null);
        $service=new PublicPulseWebhookService($repo);
        $raw='{"job_ref":"11111111-1111-4111-8111-111111111111","status":"completed"}';
        $this->expectException(ValidationException::class);
        $service->handle($raw,hash_hmac('sha256',$raw,'webhook-secret'),['job_ref'=>'11111111-1111-4111-8111-111111111111','status'=>'completed']);
    }
    public function test_terminal_job_delivery_is_idempotent(): void
    {
        config(['services.pulse_engine.webhook_secret'=>'webhook-secret']);
        $job=new PublicPulseJob(['job_ref'=>'11111111-1111-4111-8111-111111111111','status'=>PublicPulseJob::STATUS_COMPLETED]);
        $repo=$this->createMock(PublicPulseJobRepositoryInterface::class);
        $repo->method('findByJobRef')->willReturn($job);
        $repo->expects($this->never())->method('updateNonTerminal');
        $raw='{"job_ref":"11111111-1111-4111-8111-111111111111","status":"running"}';
        $result=(new PublicPulseWebhookService($repo))->handle($raw,hash_hmac('sha256',$raw,'webhook-secret'),['job_ref'=>$job->job_ref,'status'=>'running']);
        $this->assertSame($job,$result);
    }
}
