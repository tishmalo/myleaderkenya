<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class UserTokenPurchase extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = ['user_id','purchaser_name','objective','kitty_type','candidate_token_package_id','provider','checkout_reference','package_name','token_amount','price','currency','payment_reference','gateway_transaction_code','gateway_status','gateway_response','callback_received_at','status','credited_at'];
    protected $casts = ['gateway_response'=>'array','callback_received_at'=>'datetime','credited_at'=>'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function package(): BelongsTo { return $this->belongsTo(CandidateTokenPackage::class, 'candidate_token_package_id'); }
}
