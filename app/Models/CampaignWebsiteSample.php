<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignWebsiteSample extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_url',
        'website_url',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }
}
