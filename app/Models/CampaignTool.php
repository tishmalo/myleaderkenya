<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CampaignTool extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'nav_label',
        'excerpt',
        'content',
        'featured_image',
        'meta_title',
        'meta_description',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (CampaignTool $campaignTool) {
            if (empty($campaignTool->slug)) {
                $campaignTool->slug = Str::slug($campaignTool->title);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function featureRequests(): HasMany
    {
        return $this->hasMany(CampaignToolRequest::class);
    }
    public function getNavTitleAttribute(): string
    {
        return $this->nav_label ?: $this->title;
    }
}
