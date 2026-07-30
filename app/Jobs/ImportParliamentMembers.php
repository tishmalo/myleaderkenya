<?php
namespace App\Jobs;
use App\Models\ParliamentImportRun;
use App\Services\Parliament\ParliamentMemberImportService;
use App\Services\Parliament\ParliamentMembersApiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
class ImportParliamentMembers implements ShouldQueue,ShouldBeUnique
{
 use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;
 public int $tries=3; public int $timeout=180; public int $uniqueFor=3600;
 public function __construct(public int $runId){} public function uniqueId():string{return 'parliament-members-directory';} public function backoff():array{return [60,300,900];}
 public function handle(ParliamentMembersApiClient $api,ParliamentMemberImportService $importer):void
 { $run=ParliamentImportRun::findOrFail($this->runId);$run->update(['status'=>'running','started_at'=>now(),'failure_code'=>null]);$result=$importer->importDirectory($api->members());$run->update(['status'=>'complete','members_received'=>$result['received'],'members_saved'=>$result['saved'],'completed_at'=>now()]);foreach($result['ids'] as $i=>$id){FetchParliamentMemberDetail::dispatch($id)->delay(now()->addSeconds($i*(int)config('services.parliament_members.detail_spacing_seconds',2)));}}
 public function failed(?Throwable $exception):void{ParliamentImportRun::whereKey($this->runId)->update(['status'=>'failed','failure_code'=>'directory_import_failed','completed_at'=>now()]);}
}