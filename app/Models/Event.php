<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'poster',
        'promo_video',
        'date',
        'location',
        'price',
        'is_active',
        'approval_status',
        'created_by',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'date' => 'datetime',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', self::STATUS_PENDING);
    }

    public function getPromoVideoEmbedUrlAttribute(): ?string
    {
        if (! $this->promo_video) {
            return null;
        }

        $videoId = null;

        if (preg_match('/youtu\.be\/([A-Za-z0-9_-]{11})/', $this->promo_video, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/[?&]v=([A-Za-z0-9_-]{11})/', $this->promo_video, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/youtube\.com\/(?:embed|shorts|live)\/([A-Za-z0-9_-]{11})/', $this->promo_video, $matches)) {
            $videoId = $matches[1];
        }

        return $videoId ? 'https://www.youtube.com/embed/' . $videoId : null;
    }
}
