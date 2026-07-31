<?php
namespace App\Jobs;
use App\Models\ParliamentMember;
use App\Services\Parliament\ParliamentMemberImportService;
use App\Services\Parliament\ParliamentMembersApiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;
class FetchParliamentMemberDetail implements ShouldQueue
{
 use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;
 public int $tries=4; public int $timeout=120;
 public function __construct(public int $memberId){} public function backoff():array{return [60,300,900];}
 public function middleware():array{return[(new WithoutOverlapping('parliament-member-'.$this->memberId))->releaseAfter(60)->expireAfter(180)];}
 public function handle(ParliamentMembersApiClient $api,ParliamentMemberImportService $importer):void{$member=ParliamentMember::findOrFail($this->memberId);if($member->detail_status==='complete')return;$member->update(['detail_status'=>'fetching','failure_code'=>null]);$importer->importDetail($member,$api->member($member->external_slug,$member->house));}
 public function failed(?Throwable $exception):void{ParliamentMember::whereKey($this->memberId)->update(['detail_status'=>'failed','failure_code'=>'detail_fetch_failed']);}
}