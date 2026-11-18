<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\SeoPage;
use Illuminate\Support\Collection;

interface SeoPageRepositoryInterface
{
    /**
     * @return Collection<int, SeoPage>
     */
    public function all(): Collection;

    public function findByRouteName(string $routeName): ?SeoPage;

    public function update(SeoPage $seoPage, array $data): bool;
}
