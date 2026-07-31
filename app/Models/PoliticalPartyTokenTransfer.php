<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PoliticalPartyTokenTransfer extends Model { protected $guarded = []; public function candidate(){return $this->belongsTo(Candidate::class);} public function politicalParty(){return $this->belongsTo(PoliticalParty::class);} }