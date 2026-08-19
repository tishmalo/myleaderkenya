<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
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
    ];

    protected $casts = [
        'date' => 'datetime',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
