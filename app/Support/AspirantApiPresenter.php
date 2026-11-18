<?php

namespace App\Support;

use App\Models\Candidate;
use App\Models\NewsArticle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class AspirantApiPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function format(Candidate $candidate, bool $includeAbout = false): array
    {
        $data = [
            'id' => $candidate->id,
            'name' => $candidate->name,
            'nick_name' => $candidate->nick_name,
            'slug' => $candidate->slug,
            'profile_url' => route('aspirants.show', $candidate),
            'position_id' => $candidate->position_id,
            'political_party_id' => $candidate->political_party_id,
            'party' => $candidate->political_party_id,
            'featured' => (bool) $candidate->featured,
            'profile_picture' => $candidate->profile_picture,
            'profile_picture_url' => self::storageUrl($candidate->profile_picture),
            'cover_photo' => $candidate->cover_photo,
            'cover_photo_url' => self::storageUrl($candidate->cover_photo),
            'campaign_poster' => $candidate->campaign_poster,
            'campaign_poster_url' => self::storageUrl($candidate->campaign_poster),
            'campaign_video' => $candidate->campaign_video,
            'campaign_video_url' => $candidate->campaign_video_url
                ?: self::storageUrl($candidate->campaign_video),
            'campaign_song_url' => $candidate->campaign_song_url,
            'campaign_skiza_audio' => $candidate->campaign_skiza_audio,
            'campaign_skiza_audio_url' => self::storageUrl($candidate->campaign_skiza_audio),
            'social_media' => [
                'facebook_url' => $candidate->facebook_url,
                'x_url' => $candidate->x_url,
                'instagram_url' => $candidate->instagram_url,
                'tiktok_url' => $candidate->tiktok_url,
                'youtube_url' => $candidate->youtube_url,
                'whatsapp_group_url' => $candidate->whatsapp_group_url,
            ],
            'media' => [
                'campaign_video_url' => $candidate->campaign_video_url
                    ?: self::storageUrl($candidate->campaign_video),
                'campaign_song_url' => $candidate->campaign_song_url,
                'campaign_skiza_audio_url' => self::storageUrl($candidate->campaign_skiza_audio),
                'campaign_poster_url' => self::storageUrl($candidate->campaign_poster),
            ],
            'country' => self::locationValue($candidate->country),
            'county' => self::locationValue($candidate->county),
            'constituency' => self::locationValue($candidate->constituency),
            'ward' => self::locationValue($candidate->ward),
            'position' => $candidate->position ? [
                'id' => $candidate->position->id,
                'name' => $candidate->position->name,
                'slug' => $candidate->position->slug ?? null,
            ] : null,
            'political_party' => $candidate->politicalParty ? [
                'id' => $candidate->politicalParty->id,
                'name' => $candidate->politicalParty->name,
                'slug' => $candidate->politicalParty->slug,
                'abbreviation' => $candidate->politicalParty->abbreviation,
                'logo_url' => self::storageUrl($candidate->politicalParty->logo),
                'brand_color' => $candidate->politicalParty->brand_color,
            ] : null,
            'created_at' => optional($candidate->created_at)->toISOString(),
        ];

        if ($includeAbout) {
            $data['about'] = $candidate->about;
        }

        return $data;
    }

    /**
     * @param  Collection<int, NewsArticle>  $articles
     * @return Collection<int, array<string, mixed>>
     */
    public static function relatedArticles(Collection $articles): Collection
    {
        return $articles->map(fn (NewsArticle $article): array => [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'featured_image_url' => self::storageUrl($article->featured_image),
            'published_at' => optional($article->published_at)->toISOString(),
            'tags' => $article->tags->map(fn ($tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'color' => $tag->color ?? null,
            ])->values(),
        ])->values();
    }

    public static function storageUrl(?string $path): ?string
    {
        return $path ? asset(Storage::url($path)) : null;
    }

    private static function locationValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value['name'] ?? $value['label'] ?? null;
        }

        if (is_object($value)) {
            return $value->name ?? $value->label ?? null;
        }

        $value = trim((string) $value);

        if ($value === '[object Object]') {
            return null;
        }

        if (str_starts_with($value, '{')) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded['name'] ?? $decoded['label'] ?? null;
            }
        }

        return $value;
    }
}
