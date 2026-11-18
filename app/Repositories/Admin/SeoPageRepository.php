<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\SeoPageRepositoryInterface;
use App\Models\SeoPage;
use Illuminate\Support\Collection;

class SeoPageRepository implements SeoPageRepositoryInterface
{
    public function all(): Collection
    {
        return SeoPage::query()->orderBy('route_name')->get();
    }

    public function findByRouteName(string $routeName): ?SeoPage
    {
        return SeoPage::query()->where('route_name', $routeName)->first();
    }

    public function update(SeoPage $seoPage, array $data): bool
    {
        return $seoPage->update($data);
    }
}
