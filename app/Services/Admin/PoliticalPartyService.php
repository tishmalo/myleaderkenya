<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\PoliticalPartyRepositoryInterface;
use App\Models\PoliticalParty;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PoliticalPartyService
{
    public function __construct(private PoliticalPartyRepositoryInterface $politicalPartyRepository) {}

    public function getPaginatedParties(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->politicalPartyRepository->paginate($filters, $perPage);
    }

    public function getPublishedParties(int $perPage = 12): LengthAwarePaginator
    {
        return $this->politicalPartyRepository->published($perPage);
    }

    public function getPublishedNavParties(): Collection
    {
        return $this->politicalPartyRepository->publishedForNav();
    }

    public function getPublicShowData(string $slug): PoliticalParty
    {
        return $this->politicalPartyRepository->findPublishedBySlug($slug);
    }

    public function createParty(array $data, ?UploadedFile $logo = null): PoliticalParty
    {
        $data = $this->prepareData($data);

        if ($logo) {
            $data['logo'] = $logo->store('political-parties', 'public');
        }

        return $this->politicalPartyRepository->create($data);
    }

    public function updateParty(PoliticalParty $politicalParty, array $data, ?UploadedFile $logo = null): bool
    {
        $data = $this->prepareData($data);

        if ($logo) {
            if ($politicalParty->logo) {
                Storage::disk('public')->delete($politicalParty->logo);
            }

            $data['logo'] = $logo->store('political-parties', 'public');
        }

        return $this->politicalPartyRepository->update($politicalParty, $data);
    }

    public function deleteParty(PoliticalParty $politicalParty): bool
    {
        if ($politicalParty->logo) {
            Storage::disk('public')->delete($politicalParty->logo);
        }

        return $this->politicalPartyRepository->delete($politicalParty);
    }

    private function prepareData(array $data): array
    {
        $data['slug'] = Str::slug(($data['slug'] ?? '') ?: $data['name']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['abbreviation'] = $data['abbreviation'] ?: null;
        $data['brand_color'] = $data['brand_color'] ?: null;
        $data['excerpt'] = $data['excerpt'] ?: null;
        $data['website_url'] = $data['website_url'] ?: null;
        $data['meta_title'] = $data['meta_title'] ?: null;
        $data['meta_description'] = $data['meta_description'] ?: null;

        return $data;
    }
}
