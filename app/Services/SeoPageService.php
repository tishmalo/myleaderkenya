<?php

namespace App\Services;

use App\Contracts\Repositories\Admin\SeoPageRepositoryInterface;
use App\Models\SeoPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class SeoPageService
{
    public function __construct(
        private SeoPageRepositoryInterface $seoPageRepository
    ) {}

    /**
     * @return Collection<int, SeoPage>
     */
    public function getPages(): Collection
    {
        return $this->seoPageRepository->all();
    }

    public function updatePage(SeoPage $seoPage, array $data): bool
    {
        $data['canonical'] = filled($data['canonical'] ?? null) ? $data['canonical'] : null;

        return $this->seoPageRepository->update($seoPage, $data);
    }

    /**
     * Apply the metadata stored for a page (keyed by route name) to the SEO
     * service. Pages without stored metadata keep the service defaults.
     */
    public function applyTo(SeoService $seo, string $routeName): SeoService
    {
        $page = $this->seoPageRepository->findByRouteName($routeName);

        if (! $page) {
            return $seo;
        }

        $seo->setTitle($page->title)
            ->setDescription($page->description);

        $canonical = $page->canonical
            ?: (Route::has($page->route_name) ? route($page->route_name) : null);

        return $seo->setCanonical($canonical);
    }
}
