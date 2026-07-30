<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\FetchParliamentMemberDetail;
use App\Jobs\ImportParliamentMembers;
use App\Models\Candidate;
use App\Models\ParliamentImportRun;
use App\Models\ParliamentMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ParliamentMemberController extends Controller
{
    public function index(Request $request): View
    {
        $members = ParliamentMember::query()->with(['candidate:id,name,slug', 'committees', 'activities'])
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q->where('source_name','like','%'.trim((string)$request->search).'%')->orWhere('constituency','like','%'.trim((string)$request->search).'%')))
            ->when($request->filled('house'), fn ($q) => $q->where('house',$request->house))
            ->when($request->filled('detail_status'), fn ($q) => $q->where('detail_status',$request->detail_status))
            ->when($request->input('match_status')==='unmatched',fn($q)=>$q->whereNull('candidate_id')->whereNull('match_method'))
            ->when($request->input('match_status')==='ambiguous',fn($q)=>$q->where('match_method','ambiguous'))
            ->when($request->input('match_status')==='automatic',fn($q)=>$q->where('match_method','automatic'))
            ->when($request->input('match_status')==='manual',fn($q)=>$q->where('match_method','manual'))
            ->when($request->input('publication')==='published',fn($q)=>$q->where('is_published',true))
            ->when($request->input('publication')==='unpublished',fn($q)=>$q->where('is_published',false))
            ->orderBy('source_name')->paginate(30)->withQueryString();

        $counts = [
            'total'=>ParliamentMember::count(),'missing'=>ParliamentMember::whereIn('detail_status',['missing','failed'])->count(),
            'unmatched'=>ParliamentMember::whereNull('candidate_id')->count(),'ambiguous'=>ParliamentMember::where('match_method','ambiguous')->count(),
            'automatic'=>ParliamentMember::where('match_method','automatic')->count(),'manual'=>ParliamentMember::where('match_method','manual')->count(),
            'published'=>ParliamentMember::where('is_published',true)->count(),
        ];
        return view('parliament-members.index',['members'=>$members,'counts'=>$counts,'importRun'=>ParliamentImportRun::where('import_key','members-directory-v1')->first(),'houses'=>ParliamentMember::whereNotNull('house')->distinct()->orderBy('house')->pluck('house')]);
    }

    public function import(Request $request): RedirectResponse
    {
        $run=DB::transaction(function()use($request){
            $run=ParliamentImportRun::where('import_key','members-directory-v1')->lockForUpdate()->first();
            if($run && in_array($run->status,['pending','running','complete'],true)) throw ValidationException::withMessages(['import'=>'The members directory import has already been requested.']);
            return ParliamentImportRun::updateOrCreate(['import_key'=>'members-directory-v1'],['status'=>'pending','failure_code'=>null,'started_at'=>null,'completed_at'=>null,'requested_by'=>$request->user()->id]);
        });
        ImportParliamentMembers::dispatch($run->id);
        return back()->with('success','The one-time parliamentary members import was queued.');
    }

    public function link(Request $request, ParliamentMember $parliamentMember): RedirectResponse
    {
        $data=$request->validate(['candidate_id'=>['nullable','integer','exists:candidates,id']]);
        DB::transaction(function()use($data,$request,$parliamentMember):void{
            $member=ParliamentMember::lockForUpdate()->findOrFail($parliamentMember->id);
            $candidateId=$data['candidate_id']??null;
            if ($candidateId) Candidate::lockForUpdate()->findOrFail($candidateId);
            if($candidateId && ParliamentMember::where('candidate_id',$candidateId)->whereKeyNot($member->id)->exists()) throw ValidationException::withMessages(['candidate_id'=>'That candidate is already linked to another member.']);
            $member->update(['candidate_id'=>$candidateId,'match_method'=>$candidateId?'manual':null,'matched_token_count'=>0,'linked_by'=>$candidateId?$request->user()->id:null,'linked_at'=>$candidateId?now():null,'is_published'=>false,'published_by'=>null,'published_at'=>null]);
        });
        return back()->with('success','The member link was updated. Publication was reset for review.');
    }

    public function publish(Request $request, ParliamentMember $parliamentMember): RedirectResponse
    {
        $data=$request->validate(['is_published'=>['required','boolean']]);
        if($data['is_published'] && (!$parliamentMember->candidate_id || $parliamentMember->detail_status!=='complete')) throw ValidationException::withMessages(['is_published'=>'Link a candidate and complete the detail import before publishing.']);
        $parliamentMember->update(['is_published'=>$data['is_published'],'published_by'=>$data['is_published']?$request->user()->id:null,'published_at'=>$data['is_published']?now():null]);
        return back()->with('success',$data['is_published']?'Parliamentary information published.':'Parliamentary information unpublished.');
    }

    public function retry(ParliamentMember $parliamentMember): RedirectResponse
    {
        if($parliamentMember->detail_status==='complete') return back()->with('success','This member already has complete detail data.');
        $parliamentMember->update(['detail_status'=>'missing','failure_code'=>null]);
        FetchParliamentMemberDetail::dispatch($parliamentMember->id);
        return back()->with('success','The member detail retry was queued.');
    }
}