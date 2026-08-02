<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicPulseJob extends Model
{
    public const STATUS_SUBMITTING = 'submitting';
    public const STATUS_QUEUED_PENDING_CAPACITY = 'queued_pending_capacity';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_DEGRADED = 'degraded';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const TERMINAL_STATUSES = [self::STATUS_COMPLETED, self::STATUS_FAILED];

    protected $fillable = [
        'job_ref', 'engine_job_id', 'candidate_id', 'submitted_by', 'keywords', 'sources',
        'date_from', 'date_to', 'requested_limit', 'status', 'partial', 'summary',
        'error_message', 'submitted_at', 'last_synced_at', 'completed_at',
    ];

    protected $casts = [
        'keywords' => 'array',
        'sources' => 'array',
        'date_from' => 'date',
        'date_to' => 'date',
        'requested_limit' => 'integer',
        'partial' => 'boolean',
        'summary' => 'array',
        'submitted_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }
}
