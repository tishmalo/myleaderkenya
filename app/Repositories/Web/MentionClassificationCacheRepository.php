<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\MentionClassificationCacheRepositoryInterface;
use App\Models\PublicPulseMention;
use App\Models\PublicPulseMentionClassification;
use Illuminate\Support\Collection;

class MentionClassificationCacheRepository implements MentionClassificationCacheRepositoryInterface
{
    public function findReusable(PublicPulseMention $mention, string $contentHash, string $promptVersion, int $ttlDays): ?PublicPulseMentionClassification
    {
        return PublicPulseMentionClassification::query()
            ->where('candidate_id', $mention->candidate_id)
            ->where('content_hash', $contentHash)
            ->where('prompt_version', $promptVersion)
            ->where('classified_at', '>=', now()->subDays($ttlDays))
            ->first();
    }

    public function store(PublicPulseMention $mention, array $classification, string $contentHash, string $promptVersion, string $modelName, array $usage = []): PublicPulseMentionClassification
    {
        return PublicPulseMentionClassification::updateOrCreate(
            [
                'candidate_id' => $mention->candidate_id,
                'content_hash' => $contentHash,
                'prompt_version' => $promptVersion,
            ],
            [
                'mention_id' => $mention->id,
                'language' => $classification['language'] ?? 'unknown',
                'translated_summary' => $classification['translated_summary'] ?? null,
                'sentiment' => $classification['sentiment'] ?? 'neutral',
                'tone' => $classification['tone'] ?? 'unclear',
                'emotion' => $classification['emotion'] ?? 'none',
                'toxicity' => $classification['toxicity'] ?? 'none',
                'sarcasm' => (bool) ($classification['sarcasm'] ?? false),
                'topics' => $classification['topics'] ?? [],
                'stance' => $classification['stance'] ?? 'mentions_candidate',
                'confidence' => (float) ($classification['confidence'] ?? 0),
                'model_name' => $modelName,
                'input_tokens' => (int) ($usage['input_tokens'] ?? 0),
                'output_tokens' => (int) ($usage['output_tokens'] ?? 0),
                'raw_json' => $classification,
                'classified_at' => now(),
            ]
        );
    }

    public function applyToMention(PublicPulseMention $mention, PublicPulseMentionClassification $classification): void
    {
        $mention->forceFill([
            'language' => $classification->language,
            'sentiment' => $classification->sentiment,
            'tone' => $classification->tone,
            'classification_confidence' => $classification->confidence,
            'classified_at' => $classification->classified_at ?? now(),
        ])->save();
    }

    public function classificationsForMentions(Collection $mentions, string $promptVersion): Collection
    {
        return PublicPulseMentionClassification::query()
            ->whereIn('candidate_id', $mentions->pluck('candidate_id')->unique()->all())
            ->whereIn('content_hash', $mentions->pluck('content_hash')->filter()->unique()->all())
            ->where('prompt_version', $promptVersion)
            ->get()
            ->keyBy(fn (PublicPulseMentionClassification $classification): string => $classification->candidate_id.'|'.$classification->content_hash);
    }
}
