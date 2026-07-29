<?php

namespace App\Services\PublicPulse;

use App\Contracts\Repositories\Web\MentionClassificationCacheRepositoryInterface;
use App\Contracts\Repositories\Web\PublicPulseMentionRepositoryInterface;
use App\Contracts\Services\MentionLanguageDetectorInterface;
use App\Contracts\Services\MentionToneClassifierInterface;
use App\Models\PublicPulseMention;
use App\Support\HomepageCache;
use Illuminate\Support\Collection;

class PublicPulseMentionClassificationService
{
    public function __construct(
        private PublicPulseMentionRepositoryInterface $mentionRepository,
        private MentionClassificationCacheRepositoryInterface $cacheRepository,
        private MentionLanguageDetectorInterface $languageDetector,
        private MentionToneClassifierInterface $toneClassifier
    ) {}

    public function classifyPending(?int $limit = null): array
    {
        $promptVersion = $this->promptVersion();
        $ttlDays = $this->cacheTtlDays();
        $mentions = $this->mentionRepository->pendingClassification($limit ?? $this->batchSize(), $promptVersion, $ttlDays);

        return $this->classifyMentions($mentions, true);
    }

    public function classifyMentionIds(array $ids, bool $force = false): array
    {
        return $this->classifyMentions($this->mentionRepository->findMany($ids), $force);
    }

    private function classifyMentions(Collection $mentions, bool $force = false): array
    {
        $promptVersion = $this->promptVersion();
        $ttlDays = $this->cacheTtlDays();
        $queued = collect();
        $classified = 0;
        $cached = 0;
        $skipped = 0;

        foreach ($mentions as $mention) {
            $text = $this->mentionRepository->normalizedContent($mention);

            if ($text === '' || mb_strlen($text) < (int) config('services.deepseek.min_chars', 18)) {
                $this->applyLocalNeutral($mention, $promptVersion);
                $skipped++;
                continue;
            }

            $contentHash = $this->mentionRepository->contentHash($mention, $promptVersion);
            $mention->content_hash = $contentHash;

            if (! $force) {
                $cache = $this->cacheRepository->findReusable($mention, $contentHash, $promptVersion, $ttlDays);

                if ($cache) {
                    $this->cacheRepository->applyToMention($mention, $cache);
                    $cached++;
                    continue;
                }
            }

            $queued->push($mention);
        }

        foreach ($queued->chunk($this->batchSize()) as $chunk) {
            $result = $this->toneClassifier->classify($chunk);

            foreach ($chunk as $mention) {
                $classification = $result['classifications'][$mention->id] ?? null;

                if (! $classification) {
                    $this->applyLocalNeutral($mention, $promptVersion);
                    $skipped++;
                    continue;
                }

                $stored = $this->cacheRepository->store(
                    $mention,
                    $classification,
                    $this->mentionRepository->contentHash($mention, $promptVersion),
                    $promptVersion,
                    (string) config('services.deepseek.model', 'deepseek-v4-flash'),
                    $result['usage'] ?? []
                );

                $this->cacheRepository->applyToMention($mention, $stored);
                $classified++;
            }
        }

        if (($classified + $cached + $skipped) > 0) {
            HomepageCache::flush();
        }

        return [
            'classified' => $classified,
            'cached' => $cached,
            'skipped' => $skipped,
        ];
    }

    private function applyLocalNeutral(PublicPulseMention $mention, string $promptVersion): void
    {
        $text = $this->mentionRepository->normalizedContent($mention);
        $classification = [
            'language' => $this->languageDetector->detect($text),
            'translated_summary' => null,
            'sentiment' => 'neutral',
            'tone' => 'informational',
            'emotion' => 'none',
            'toxicity' => 'none',
            'sarcasm' => false,
            'topics' => [],
            'stance' => 'mentions_candidate',
            'confidence' => 0.45,
        ];

        $stored = $this->cacheRepository->store(
            $mention,
            $classification,
            $this->mentionRepository->contentHash($mention, $promptVersion),
            $promptVersion,
            'local-heuristic',
            []
        );

        $this->cacheRepository->applyToMention($mention, $stored);
    }

    private function promptVersion(): string
    {
        return (string) config('services.deepseek.prompt_version', 'pulse-tone-v1');
    }

    private function cacheTtlDays(): int
    {
        return (int) config('services.deepseek.cache_ttl_days', 90);
    }

    private function batchSize(): int
    {
        return max(1, min(50, (int) config('services.deepseek.batch_size', 30)));
    }
}
