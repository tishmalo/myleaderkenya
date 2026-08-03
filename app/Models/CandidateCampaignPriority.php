<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateCampaignPriority extends Model implements AuditableContract
{
    use AuditsChanges;
    protected $fillable = [
        'candidate_id', 'campaign_priority_category_id', 'manifesto', 'status', 'sort_order',
        'submitted_by', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'candidate_id' => 'integer',
        'campaign_priority_category_id' => 'integer',
        'sort_order' => 'integer',
        'submitted_by' => 'integer',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function candidate(): BelongsTo { return $this->belongsTo(Candidate::class); }
    public function category(): BelongsTo { return $this->belongsTo(CampaignPriorityCategory::class, 'campaign_priority_category_id'); }
    public function submitter(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
