<?php

namespace App\Services\Web;

use App\Models\Candidate;
use App\Services\SeoService;

/**
 * Metadata for the public aspirant directory and profile pages.
 */
class CandidateSeoService
{
    private const FALLBACK_IMAGE = 'images/myleader.png';

    private const MAX_TITLE_LENGTH = 60;

    public function __construct(
        private SeoService $seo
    ) {}

    public function applyForIndex(): void
    {
        $this->seo
            ->setTitle('Kenya 2027 Aspirants & Candidates | My Leader Kenya')
            ->setDescription('Browse all approved Kenya 2027 aspirants by position: President, Governor, Senator, Women Rep, MP and MCA. Compare profiles and parties on My Leader Kenya.')
            ->setCanonical(route('aspirants.public'));
    }

    public function applyForShow(Candidate $candidate): void
    {
        $position = $candidate->position->name ?? 'Aspirant';
        $location = $candidate->display_area ?? $candidate->county?->name ?? 'Kenya';
        $image = $candidate->profile_picture_url ?? $candidate->cover_photo_url ?? asset(self::FALLBACK_IMAGE);
        $title = $this->title($candidate, $position, $location);
        $description = "{$candidate->name} is a {$position} aspirant for {$location} in the Kenya 2027 General Election. View profile, manifesto, news and updates on My Leader Kenya.";

        $this->seo
            ->setTitle($title)
            ->setDescription($description)
            ->setCanonical(route('aspirants.show', $candidate))
            ->setOg([
                'title' => $title,
                'description' => $description,
                'type' => 'profile',
                'image' => $image,
            ]);

        $this->seo->addSchema([
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $candidate->name,
            'jobTitle' => $position,
            'url' => route('aspirants.show', $candidate),
            'image' => $image,
            'description' => $description,
            'affiliation' => $candidate->politicalParty ? [
                '@type' => 'Organization',
                'name' => $candidate->politicalParty->name,
            ] : null,
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $location,
                'addressCountry' => 'KE',
            ],
        ]);
    }

    private function title(Candidate $candidate, string $position, string $location): string
    {
        $title = "{$candidate->name} – {$position}, {$location} | Kenya 2027";

        return strlen($title) > self::MAX_TITLE_LENGTH
            ? "{$candidate->name} – {$position} | Kenya 2027 Candidate"
            : $title;
    }
}
