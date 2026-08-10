<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CampaignToolRequest extends Model implements AuditableContract
{
    use AuditsChanges;
    public const STATUSES = ['new', 'in_progress', 'completed', 'cancelled'];

    protected $fillable = [
        'campaign_tool_id',
        'campaign_tool_package_id',
        'user_id',
        'candidate_id',
        'request_type',
        'fulfilment_type',
        'tool_key',
        'tool_title',
        'requester_name',
        'email',
        'phone',
        'requested_feature',
        'use_case',
        'disabled_reason',
        'status',
        'admin_notes',
        'tokens_required',
        'payment_status',
        'user_token_transaction_id',
        'paid_at',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return ['tokens_required'=>'integer','paid_at'=>'datetime','refunded_at'=>'datetime'];
    }

    public function campaignTool(): BelongsTo
    {
        return $this->belongsTo(CampaignTool::class);
    }
    public function package(): BelongsTo { return $this->belongsTo(CampaignToolPackage::class, 'campaign_tool_package_id'); }
    public function payment() { return $this->hasOne(CampaignToolPayment::class); }

    public function selectedTools(): BelongsToMany
    {
        return $this->belongsToMany(
            CampaignTool::class,
            'campaign_tool_request_selected_tools'
        )->withTimestamps();
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
    public function userTokenTransaction(): BelongsTo
    {
        return $this->belongsTo(UserTokenTransaction::class);
    }
}
