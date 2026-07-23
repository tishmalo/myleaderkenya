<?php

namespace App\Observers;

use App\Models\Candidate;
use App\Support\HomepageCache;

class CandidateObserver
{
    public function saved(Candidate $candidate): void
    {
        HomepageCache::flush();
    }

    public function deleted(Candidate $candidate): void
    {
        HomepageCache::flush();
    }

    public function restored(Candidate $candidate): void
    {
        HomepageCache::flush();
    }

    public function forceDeleted(Candidate $candidate): void
    {
        HomepageCache::flush();
    }
}
