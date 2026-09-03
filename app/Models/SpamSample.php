<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpamSample extends Model
{
    protected $fillable = [
        'payload', 'text_hash', 'reason', 'ip', 'source', 'campaign_tool_request_id',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function campaignToolRequest(): BelongsTo
    {
        return $this->belongsTo(CampaignToolRequest::class, 'campaign_tool_request_id');
    }
}