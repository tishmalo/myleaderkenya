<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PoliticalPartyTokenTransaction extends Model { protected $guarded = []; protected $casts=['metadata'=>'array','finalized_at'=>'datetime']; public function candidate(){return $this->belongsTo(Candidate::class);} }