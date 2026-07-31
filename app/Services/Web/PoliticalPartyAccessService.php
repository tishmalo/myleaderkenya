<?php
namespace App\Services\Web;
use App\Models\PoliticalParty;
use App\Models\User;
class PoliticalPartyAccessService {
 public function membership(User $user): ?object { return $user->politicalParties()->wherePivot('status','active')->first(); }
 public function authorize(User $user, PoliticalParty $party, bool $adminOnly=false): void { $membership=$user->politicalParties()->whereKey($party->id)->wherePivot('status','active')->first(); abort_unless($membership,403); if($adminOnly) abort_unless($membership->pivot->role==='party_admin',403); }
}