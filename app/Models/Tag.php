<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    public function articles()
    {
        return $this->belongsToMany(NewsArticle::class, 'news_article_tag');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
