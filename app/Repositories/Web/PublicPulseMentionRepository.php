<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\PublicPulseMentionRepositoryInterface;
use App\Models\PublicPulseMention;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicPulseMentionRepository implements PublicPulseMentionRepositoryInterface
{
    public function pendingClassification(int $limit, string $promptVersion, int $ttlDays): Collection
    {
        return PublicPulseMention::query()
            ->with(['candidate:id,name,nick_name'])
            ->where(function ($query): void {
                $query->whereNull('classified_at')
                    ->orWhereNull('classification_confidence');
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->filter(fn (PublicPulseMention $mention): bool => $this->normalizedContent($mention) !== '')
            ->values();
    }

    public function findMany(array $ids): Collection
    {
        return PublicPulseMention::query()
            ->with(['candidate:id,name,nick_name'])
            ->whereIn('id', $ids)
            ->get();
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return PublicPulseMention::query()
            ->with(['candidate:id,name,nick_name', 'classification'])
            ->when($filters['language'] ?? null, fn ($query, $language) => $query->where('language', $language))
            ->when($filters['tone'] ?? null, fn ($query, $tone) => $query->where('tone', $tone))
            ->when($filters['sentiment'] ?? null, fn ($query, $sentiment) => $query->where('sentiment', $sentiment))
            ->when($filters['low_confidence'] ?? null, fn ($query) => $query->where(function ($inner): void {
                $inner->whereNull('classification_confidence')
                    ->orWhere('classification_confidence', '<', 0.55);
            }))
            ->when($filters['topic'] ?? null, fn ($query, $topic) => $query->whereHas('classification', function ($classificationQuery) use ($topic): void {
                $classificationQuery->where('topics', 'like', '%"'.$topic.'"%');
            }))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('text', 'like', "%{$search}%")
                        ->orWhere('author_name', 'like', "%{$search}%")
                        ->orWhereHas('candidate', fn ($candidateQuery) => $candidateQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function markClassified(PublicPulseMention $mention, array $attributes): void
    {
        $mention->forceFill($attributes)->save();
    }

    public function normalizedContent(PublicPulseMention $mention): string
    {
        $text = trim((string) ($mention->text ?: $mention->title));

        return Str::of($text)
            ->lower()
            ->replaceMatches('/https?:\/\/\S+/u', ' ')
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->value();
    }

    public function contentHash(PublicPulseMention $mention, string $promptVersion): string
    {
        return hash('sha256', $this->normalizedContent($mention).'|'.$mention->candidate_id.'|'.$promptVersion);
    }
}
