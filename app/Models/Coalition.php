<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Coalition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'logo', 'brand_color', 'excerpt', 'content',
        'meta_title', 'meta_description', 'status', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Coalition $coalition) {
            if (empty($coalition->slug)) {
                $coalition->slug = Str::slug($coalition->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function politicalParties()
    {
        return $this->belongsToMany(PoliticalParty::class, 'coalition_political_party');
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
        return $this->name;
    }
}
