<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PoliticalParty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'abbreviation', 'logo', 'brand_color', 'excerpt', 'content',
        'website_url', 'meta_title', 'meta_description', 'status', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (PoliticalParty $party) {
            if (empty($party->slug)) {
                $party->slug = Str::slug($party->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function coalitions()
    {
        return $this->belongsToMany(Coalition::class, 'coalition_political_party');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getNavTitleAttribute(): string
    {
        return $this->abbreviation ?: $this->name;
    }
}
