<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class CampaignToolPayment extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = ['campaign_tool_request_id','campaign_tool_package_id','user_id','candidate_id','provider','checkout_reference','package_name','entitlement_type','entitlement_quantity','duration_days','gross_amount','commission_rate','platform_revenue','fulfilment_payable','refunded_amount','currency','status','payment_reference','gateway_transaction_code','gateway_status','gateway_response','callback_received_at','funded_at','refunded_at','fulfilled_at'];
    protected $casts = ['gross_amount'=>'decimal:2','commission_rate'=>'decimal:2','platform_revenue'=>'decimal:2','fulfilment_payable'=>'decimal:2','refunded_amount'=>'decimal:2','gateway_response'=>'array','callback_received_at'=>'datetime','funded_at'=>'datetime','refunded_at'=>'datetime','fulfilled_at'=>'datetime'];
    public function request(): BelongsTo { return $this->belongsTo(CampaignToolRequest::class, 'campaign_tool_request_id'); }
    public function package(): BelongsTo { return $this->belongsTo(CampaignToolPackage::class, 'campaign_tool_package_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function candidate(): BelongsTo { return $this->belongsTo(Candidate::class); }
    public function ledgerEntries(): HasMany { return $this->hasMany(CampaignToolFinancialLedger::class); }
}
