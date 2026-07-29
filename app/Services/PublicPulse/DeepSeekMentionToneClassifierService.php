<?php

namespace App\Services\PublicPulse;

use App\Contracts\Repositories\Web\PublicPulseMentionRepositoryInterface;
use App\Contracts\Services\MentionLanguageDetectorInterface;
use App\Contracts\Services\MentionToneClassifierInterface;
use App\Models\PublicPulseMention;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeepSeekMentionToneClassifierService implements MentionToneClassifierInterface
{
    public function __construct(
        private MentionLanguageDetectorInterface $languageDetector,
        private PublicPulseMentionRepositoryInterface $mentionRepository
    ) {}

    public function classify(Collection $mentions, array $context = []): array
    {
        if ($mentions->isEmpty()) {
            return ['classifications' => [], 'usage' => []];
        }

        $apiKey = config('services.deepseek.api_key');

        if (! $apiKey || ! config('services.deepseek.enabled', false)) {
            return $this->heuristicClassifications($mentions);
        }

        $payload = $this->payload($mentions, $context);

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->connectTimeout((int) config('services.deepseek.connect_timeout', 10))
                ->timeout((int) config('services.deepseek.timeout', 60))
                ->post(rtrim((string) config('services.deepseek.base_url', 'https://api.deepseek.com'), '/').'/chat/completions', [
                    'model' => config('services.deepseek.model', 'deepseek-v4-flash'),
                    'thinking' => ['type' => 'disabled'],
                    'response_format' => ['type' => 'json_object'],
                    'max_tokens' => (int) config('services.deepseek.max_tokens', 3500),
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user', 'content' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
                    ],
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('DeepSeek mention classification connection failed.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->heuristicClassifications($mentions);
        }

        if (! $response->successful()) {
            Log::warning('DeepSeek mention classification failed.', [
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 500),
            ]);

            return $this->heuristicClassifications($mentions);
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        $decoded = is_string($content) ? json_decode($content, true) : null;

        if (! is_array($decoded) || ! isset($decoded['items']) || ! is_array($decoded['items'])) {
            Log::warning('DeepSeek mention classification returned malformed JSON.', [
                'content' => Str::limit((string) $content, 500),
            ]);

            return $this->heuristicClassifications($mentions);
        }

        $classifications = [];

        foreach ($decoded['items'] as $item) {
            $id = (int) ($item['id'] ?? 0);

            if ($id > 0) {
                $classifications[$id] = $this->sanitizeClassification($item);
            }
        }

        return [
            'classifications' => $classifications,
            'usage' => [
                'input_tokens' => (int) data_get($response->json(), 'usage.prompt_tokens', 0),
                'output_tokens' => (int) data_get($response->json(), 'usage.completion_tokens', 0),
            ],
        ];
    }

    private function payload(Collection $mentions, array $context): array
    {
        return [
            'instruction' => 'Return json only.',
            'context' => [
                'country' => 'Kenya',
                'domain' => 'public political pulse',
                'candidate_aliases' => $context['candidate_aliases'] ?? [],
            ],
            'items' => $mentions->map(function (PublicPulseMention $mention): array {
                $text = $this->mentionRepository->normalizedContent($mention);

                return [
                    'id' => $mention->id,
                    'source' => $mention->source_key,
                    'candidate' => $mention->candidate?->name,
                    'candidate_nick_name' => $mention->candidate?->nick_name,
                    'local_language_hint' => $this->languageDetector->detect($text),
                    'text' => Str::limit($text, (int) config('services.deepseek.max_chars_per_mention', 800), ''),
                ];
            })->values()->all(),
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You classify public political mentions from Kenya. Output strict json with this shape:
{"items":[{"id":123,"language":"en|sw|sheng|mixed|unknown","translated_summary":null,"sentiment":"positive|neutral|negative|mixed","tone":"supportive|critical|mocking|angry|concerned|informational|campaigning|attack|unclear","stance":"supports_candidate|opposes_candidate|mentions_candidate|compares_candidates","emotion":"hope|trust|anger|fear|disgust|ridicule|none","toxicity":"none|low|medium|high","sarcasm":false,"topics":["economy"],"confidence":0.8}]}
Use translated_summary only for non-English or mixed-language content. Keep it short. Allowed topics: economy, corruption, healthcare, education, security, ethnicity_region, scandal, endorsement, policy, campaign_event. If uncertain, use neutral, unclear, none, mentions_candidate, and low confidence. Do not include reasoning.
PROMPT;
    }

    private function heuristicClassifications(Collection $mentions): array
    {
        $classifications = [];

        foreach ($mentions as $mention) {
            $text = $this->mentionRepository->normalizedContent($mention);
            $classifications[$mention->id] = [
                'language' => $this->languageDetector->detect($text),
                'translated_summary' => null,
                'sentiment' => 'neutral',
                'tone' => $this->languageDetector->isObviousNeutral($text) ? 'informational' : 'unclear',
                'emotion' => 'none',
                'toxicity' => 'none',
                'sarcasm' => false,
                'topics' => $this->topicsFromText($text),
                'stance' => 'mentions_candidate',
                'confidence' => $this->languageDetector->isObviousNeutral($text) ? 0.45 : 0.25,
            ];
        }

        return ['classifications' => $classifications, 'usage' => ['input_tokens' => 0, 'output_tokens' => 0]];
    }

    private function sanitizeClassification(array $classification): array
    {
        return [
            'language' => $this->allowed($classification['language'] ?? null, ['en', 'sw', 'sheng', 'mixed', 'unknown'], 'unknown'),
            'translated_summary' => isset($classification['translated_summary']) ? Str::limit((string) $classification['translated_summary'], 700) : null,
            'sentiment' => $this->allowed($classification['sentiment'] ?? null, ['positive', 'neutral', 'negative', 'mixed'], 'neutral'),
            'tone' => $this->allowed($classification['tone'] ?? null, ['supportive', 'critical', 'mocking', 'angry', 'concerned', 'informational', 'campaigning', 'attack', 'unclear'], 'unclear'),
            'emotion' => $this->allowed($classification['emotion'] ?? null, ['hope', 'trust', 'anger', 'fear', 'disgust', 'ridicule', 'none'], 'none'),
            'toxicity' => $this->allowed($classification['toxicity'] ?? null, ['none', 'low', 'medium', 'high'], 'none'),
            'sarcasm' => (bool) ($classification['sarcasm'] ?? false),
            'topics' => collect($classification['topics'] ?? [])->filter()->values()->take(6)->all(),
            'stance' => $this->allowed($classification['stance'] ?? null, ['supports_candidate', 'opposes_candidate', 'mentions_candidate', 'compares_candidates'], 'mentions_candidate'),
            'confidence' => max(0, min(1, (float) ($classification['confidence'] ?? 0))),
        ];
    }

    private function allowed(?string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function topicsFromText(string $text): array
    {
        $topics = [];

        foreach ([
            'economy' => ['economy', 'jobs', 'tax', 'hustler', 'unga', 'fuel'],
            'corruption' => ['corruption', 'graft', 'scandal', 'loot'],
            'healthcare' => ['health', 'hospital', 'sha', 'nhif'],
            'education' => ['school', 'education', 'university', 'cbc'],
            'security' => ['security', 'police', 'crime'],
            'campaign_event' => ['rally', 'campaign', 'meeting'],
        ] as $topic => $words) {
            if (Str::contains($text, $words)) {
                $topics[] = $topic;
            }
        }

        return $topics;
    }
}
