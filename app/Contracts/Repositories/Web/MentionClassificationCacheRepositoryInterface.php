<?php

namespace App\Contracts\Repositories\Web;

use App\Models\PublicPulseMention;
use App\Models\PublicPulseMentionClassification;
use Illuminate\Support\Collection;

interface MentionClassificationCacheRepositoryInterface
{
    public function findReusable(PublicPulseMention $mention, string $contentHash, string $promptVersion, int $ttlDays): ?PublicPulseMentionClassification;

    public function store(PublicPulseMention $mention, array $classification, string $contentHash, string $promptVersion, string $modelName, array $usage = []): PublicPulseMentionClassification;

    public function applyToMention(PublicPulseMention $mention, PublicPulseMentionClassification $classification): void;

    public function classificationsForMentions(Collection $mentions, string $promptVersion): Collection;
}
