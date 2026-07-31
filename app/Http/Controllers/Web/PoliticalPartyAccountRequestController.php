<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\PoliticalParty;
use App\Models\PoliticalPartyAccountRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class PoliticalPartyAccountRequestController extends Controller {
 public function create(PoliticalParty $politicalParty){return view('political-parties.account-request',compact('politicalParty'));}
 public function store(Request $request, PoliticalParty $politicalParty){$data=$request->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255','phone'=>'required|string|max:50','party_title'=>'required|string|max:150','password'=>'required|string|min:8|confirmed','authorization_document'=>'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);$hash=hash('sha256',strtolower(trim($data['email'])));abort_if(PoliticalPartyAccountRequest::where('political_party_id',$politicalParty->id)->where('email',$data['email'])->where('status','pending')->exists(),422,'A pending request already exists for this email.');$user=User::where('email_hash',$hash)->first()?:User::create(['name'=>$data['name'],'email'=>$data['email'],'phone'=>$data['phone'],'password'=>Hash::make($data['password']),'role'=>'user']);$path=$request->file('authorization_document')->store('party-authorizations');PoliticalPartyAccountRequest::create(['political_party_id'=>$politicalParty->id,'user_id'=>$user->id,'name'=>$data['name'],'email'=>strtolower($data['email']),'phone'=>$data['phone'],'party_title'=>$data['party_title'],'authorization_document'=>$path]);return redirect()->route('parties.show',$politicalParty)->with('success','Party dashboard access request submitted for admin review.');}
}