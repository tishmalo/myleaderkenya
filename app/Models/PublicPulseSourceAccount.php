<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicPulseSourceAccount extends Model
{
    public const STATUS_HEALTHY = 'healthy';
    public const STATUS_RATE_LIMITED = 'rate_limited';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CHECKPOINT_REQUIRED = 'checkpoint_required';
    public const STATUS_SUSPENDED_OR_BANNED = 'suspended_or_banned';
    public const STATUS_SHADOW_LIMITED = 'shadow_limited';
    public const STATUS_INVALID_SESSION = 'invalid_session';
    public const STATUS_NEEDS_REPLACEMENT = 'needs_replacement';
    public const STATUS_UNKNOWN_ERROR = 'unknown_error';

    public const STATUSES = [
        self::STATUS_HEALTHY,
        self::STATUS_RATE_LIMITED,
        self::STATUS_EXPIRED,
        self::STATUS_CHECKPOINT_REQUIRED,
        self::STATUS_SUSPENDED_OR_BANNED,
        self::STATUS_SHADOW_LIMITED,
        self::STATUS_INVALID_SESSION,
        self::STATUS_NEEDS_REPLACEMENT,
        self::STATUS_UNKNOWN_ERROR,
    ];

    protected $fillable = [
        'source_key',
        'provider',
        'label',
        'username',
        'encrypted_session_payload',
        'status',
        'last_health_check_at',
        'last_success_at',
        'failure_count',
        'consecutive_failure_count',
        'last_error_code',
        'last_error_message',
        'last_result_count',
        'median_result_ratio',
        'cooldown_until',
        'issue_notified_at',
        'replaced_at',
        'notes',
    ];

    protected $casts = [
        'encrypted_session_payload' => 'encrypted',
        'last_health_check_at' => 'datetime',
        'last_success_at' => 'datetime',
        'failure_count' => 'integer',
        'consecutive_failure_count' => 'integer',
        'last_result_count' => 'integer',
        'median_result_ratio' => 'float',
        'cooldown_until' => 'datetime',
        'issue_notified_at' => 'datetime',
        'replaced_at' => 'datetime',
    ];

    public function isUsable(): bool
    {
        if ($this->status !== self::STATUS_HEALTHY) {
            return false;
        }

        return ! $this->cooldown_until || $this->cooldown_until->isPast();
    }

    public function needsNotification(): bool
    {
        if ($this->status === self::STATUS_HEALTHY) {
            return false;
        }

        return ! $this->issue_notified_at || $this->issue_notified_at->lt(now()->subHours(6));
    }
}
