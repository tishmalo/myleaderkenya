<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CoalitionRepositoryInterface;
use App\Models\Coalition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CoalitionService
{
    public function __construct(private CoalitionRepositoryInterface $coalitionRepository) {}

    public function getPaginatedCoalitions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->coalitionRepository->paginate($filters, $perPage);
    }

    public function getPublishedCoalitions(int $perPage = 12): LengthAwarePaginator
    {
        return $this->coalitionRepository->published($perPage);
    }

    public function getPublishedNavCoalitions(): Collection
    {
        return $this->coalitionRepository->publishedForNav();
    }

    public function getFormData(): array
    {
        return ['politicalParties' => $this->coalitionRepository->allPoliticalParties()];
    }

    public function getPublicShowData(string $slug): Coalition
    {
        return $this->coalitionRepository->findPublishedBySlug($slug);
    }

    public function createCoalition(array $data, ?UploadedFile $logo = null, array $politicalParties = []): Coalition
    {
        $data = $this->prepareData($data);

        if ($logo) {
            $data['logo'] = $logo->store('coalitions', 'public');
        }

        $coalition = $this->coalitionRepository->create($data);
        $this->coalitionRepository->syncPoliticalParties($coalition, $politicalParties);

        return $coalition;
    }

    public function updateCoalition(Coalition $coalition, array $data, ?UploadedFile $logo = null, array $politicalParties = []): bool
    {
        $data = $this->prepareData($data);

        if ($logo) {
            if ($coalition->logo) {
                Storage::disk('public')->delete($coalition->logo);
            }

            $data['logo'] = $logo->store('coalitions', 'public');
        }

        $success = $this->coalitionRepository->update($coalition, $data);
        $this->coalitionRepository->syncPoliticalParties($coalition, $politicalParties);

        return $success;
    }

    public function deleteCoalition(Coalition $coalition): bool
    {
        if ($coalition->logo) {
            Storage::disk('public')->delete($coalition->logo);
        }

        return $this->coalitionRepository->delete($coalition);
    }

    private function prepareData(array $data): array
    {
        $data['slug'] = Str::slug(($data['slug'] ?? '') ?: $data['name']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['brand_color'] = $data['brand_color'] ?: null;
        $data['excerpt'] = $data['excerpt'] ?: null;
        $data['meta_title'] = $data['meta_title'] ?: null;
        $data['meta_description'] = $data['meta_description'] ?: null;

        return $data;
    }
}
