<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignToolFinancialLedger extends Model
{
    protected $table = 'campaign_tool_financial_ledger';
    protected $fillable = ['campaign_tool_payment_id','entry_type','gross_amount','platform_amount','fulfilment_amount','currency','correlation_id','metadata','occurred_at'];
    protected $casts = ['gross_amount'=>'decimal:2','platform_amount'=>'decimal:2','fulfilment_amount'=>'decimal:2','metadata'=>'array','occurred_at'=>'datetime'];
    public function payment(): BelongsTo { return $this->belongsTo(CampaignToolPayment::class, 'campaign_tool_payment_id'); }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Campaign-tool financial ledger entries are append-only.'));
        static::deleting(fn () => throw new \LogicException('Campaign-tool financial ledger entries are append-only.'));
    }
}
