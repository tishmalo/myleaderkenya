<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignToolRequest extends Model
{
    public const STATUSES = ['new', 'in_progress', 'completed', 'cancelled'];

    protected $fillable = [
        'campaign_tool_id',
        'user_id',
        'candidate_id',
        'requester_name',
        'email',
        'phone',
        'requested_feature',
        'use_case',
        'status',
        'admin_notes',
    ];

    public function campaignTool(): BelongsTo
    {
        return $this->belongsTo(CampaignTool::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}