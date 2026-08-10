<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class CandidateCampaignToolEntitlement extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = ['candidate_id','campaign_tool_id','campaign_tool_package_id','campaign_tool_payment_id','tool_key','entitlement_type','allowance','remaining_allowance','status','activated_at','expires_at','fulfilled_at','activated_by'];
    protected $casts = ['allowance'=>'integer','remaining_allowance'=>'integer','activated_at'=>'datetime','expires_at'=>'datetime','fulfilled_at'=>'datetime'];
    public function candidate(): BelongsTo { return $this->belongsTo(Candidate::class); }
    public function campaignTool(): BelongsTo { return $this->belongsTo(CampaignTool::class); }
    public function package(): BelongsTo { return $this->belongsTo(CampaignToolPackage::class, 'campaign_tool_package_id'); }
    public function payment(): BelongsTo { return $this->belongsTo(CampaignToolPayment::class, 'campaign_tool_payment_id'); }
}
