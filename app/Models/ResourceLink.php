<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ResourceLink extends Model implements AuditableContract
{
    use AuditsChanges;
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const PLATFORMS = [
        'facebook_page' => 'Facebook Page',
        'facebook_group' => 'Facebook Group',
        'facebook_account' => 'Facebook Account',
        'whatsapp_group' => 'WhatsApp Group',
        'linkedin' => 'LinkedIn',
        'instagram' => 'Instagram',
        'telegram' => 'Telegram',
        'youtube' => 'YouTube',
        'x' => 'X (Twitter)',
        'other' => 'Other',
    ];

    protected $fillable = [
        'user_id',
        'platform',
        'title',
        'url',
        'county_id',
        'constituency_id',
        'ward_id',
        'political_party_id',
        'candidate_id',
        'followers',
        'comment',
        'approval_status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'followers' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }

    public function constituency(): BelongsTo
    {
        return $this->belongsTo(Constituency::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function politicalParty(): BelongsTo
    {
        return $this->belongsTo(PoliticalParty::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approval_status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('approval_status', self::STATUS_PENDING);
    }

    public function getPlatformLabelAttribute(): string
    {
        return self::PLATFORMS[$this->platform] ?? $this->platform;
    }

    public function getPlatformIconAttribute(): string
    {
        return match ($this->platform) {
            'facebook_page', 'facebook_group', 'facebook_account' => 'fa-brands fa-facebook-f',
            'whatsapp_group' => 'fa-brands fa-whatsapp',
            'linkedin' => 'fa-brands fa-linkedin-in',
            'instagram' => 'fa-brands fa-instagram',
            'telegram' => 'fa-brands fa-telegram',
            'youtube' => 'fa-brands fa-youtube',
            'x' => 'fa-brands fa-x-twitter',
            default => 'fas fa-link',
        };
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: $this->platform_label;
    }
}
