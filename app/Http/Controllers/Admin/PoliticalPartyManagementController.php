<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\PoliticalPartyAccountRequest;
use App\Models\PoliticalPartyCandidateClaim;
use App\Models\PoliticalParty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
class PoliticalPartyManagementController extends Controller {
 public function index(){return view('political-parties.management',['accountRequests'=>PoliticalPartyAccountRequest::with(['politicalParty','user'])->latest()->paginate(20,['*'],'accounts'),'candidateClaims'=>PoliticalPartyCandidateClaim::with(['politicalParty','candidate.politicalParty','requester'])->latest()->paginate(20,['*'],'claims'),'parties'=>PoliticalParty::with('officials')->ordered()->get()]);}
 public function storeOfficial(Request $r){$data=$r->validate(['political_party_id'=>'required|exists:political_parties,id','name'=>'required|string|max:255','email'=>'required|email','password'=>'required|min:8','role'=>'required|in:party_admin,party_staff']);$user=User::where('email_hash',hash('sha256',strtolower(trim($data['email']))))->first()?:User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'role'=>'user']);PoliticalParty::findOrFail($data['political_party_id'])->officials()->syncWithoutDetaching([$user->id=>['role'=>$data['role'],'status'=>'active']]);return back()->with('success','Party official access created.');}
 public function status(Request $r,PoliticalParty $politicalParty,User $user){$data=$r->validate(['status'=>'required|in:active,suspended']);$politicalParty->officials()->updateExistingPivot($user->id,['status'=>$data['status']]);return back()->with('success','Party official status updated.');}
 public function account(Request $r,PoliticalPartyAccountRequest $accountRequest){$data=$r->validate(['status'=>'required|in:approved,rejected','review_notes'=>'nullable|string']);DB::transaction(function()use($r,$data,$accountRequest){$accountRequest->update($data+['reviewed_by'=>$r->user()->id,'reviewed_at'=>now()]);if($data['status']==='approved'&&$accountRequest->user_id)$accountRequest->politicalParty->officials()->syncWithoutDetaching([$accountRequest->user_id=>['role'=>'party_admin','status'=>'active']]);});return back()->with('success','Party account request reviewed.');}
 public function claim(Request $r,PoliticalPartyCandidateClaim $claim){$data=$r->validate(['status'=>'required|in:approved,rejected','review_notes'=>'nullable|string']);DB::transaction(function()use($r,$data,$claim){$claim->update($data+['reviewed_by'=>$r->user()->id,'reviewed_at'=>now()]);if($data['status']==='approved')$claim->candidate()->update(['political_party_id'=>$claim->political_party_id]);});return back()->with('success','Party aspirant claim reviewed.');}
 public function document(PoliticalPartyAccountRequest $accountRequest){abort_unless(Storage::exists($accountRequest->authorization_document),404);return Storage::download($accountRequest->authorization_document);}
}