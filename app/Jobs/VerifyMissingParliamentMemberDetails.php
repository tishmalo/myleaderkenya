<?php
namespace App\Jobs;
use App\Models\ParliamentMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
class VerifyMissingParliamentMemberDetails implements ShouldQueue,ShouldBeUnique
{
 use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;
 public int $timeout=300; public int $uniqueFor=7200; public function uniqueId():string{return 'verify-missing-parliament-details';}
 public function handle():void{$offset=0;ParliamentMember::query()->where(function ($query): void { $query->whereIn('detail_status',['missing','failed'])->orWhere(function ($query): void { $query->where('detail_status','fetching')->where('updated_at','<',now()->subHours(6)); }); })->orderBy('id')->chunkById(100,function($members)use(&$offset):void{foreach($members as $member){FetchParliamentMemberDetail::dispatch($member->id)->delay(now()->addSeconds($offset++*(int)config('services.parliament_members.detail_spacing_seconds',2)));}});}
}