<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class CampaignToolPackage extends Model implements AuditableContract
{
    use AuditsChanges;

    public const ENTITLEMENT_TYPES = ['time', 'quantity', 'one_time', 'permanent'];

    protected $fillable = ['campaign_tool_id','name','description','token_cost','entitlement_type','entitlement_quantity','duration_days','fulfilment_instructions','is_active','sort_order'];
    protected $casts = ['token_cost'=>'integer','is_active'=>'boolean','entitlement_quantity'=>'integer','duration_days'=>'integer','sort_order'=>'integer'];

    public function campaignTool(): BelongsTo { return $this->belongsTo(CampaignTool::class); }
    public function payments(): HasMany { return $this->hasMany(CampaignToolPayment::class); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
    public function scopeOrdered(Builder $query): Builder { return $query->orderBy('sort_order')->orderBy('token_cost')->orderBy('name'); }
}
