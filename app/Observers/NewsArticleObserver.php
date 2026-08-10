<?php

namespace App\Observers;

use App\Models\NewsArticle;
use App\Support\HomepageCache;

class NewsArticleObserver
{
    public function saved(NewsArticle $article): void
    {
        HomepageCache::flush();
    }

    public function deleted(NewsArticle $article): void
    {
        HomepageCache::flush();
    }

    public function restored(NewsArticle $article): void
    {
        HomepageCache::flush();
    }

    public function forceDeleted(NewsArticle $article): void
    {
        HomepageCache::flush();
    }
}
