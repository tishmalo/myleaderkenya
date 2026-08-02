<?php
namespace App\Jobs;
use App\Services\PublicPulse\PublicPulseJobSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
class SyncPublicPulseJobs implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $uniqueFor = 300;
    public function uniqueId(): string { return 'public-pulse-active-jobs'; }
    public function handle(PublicPulseJobSyncService $service): void { $service->syncDue(); }
}
