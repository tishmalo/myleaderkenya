<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CampaignToolRepositoryInterface;
use App\Models\CampaignTool;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CampaignToolService
{
    public function __construct(
        private CampaignToolRepositoryInterface $campaignToolRepository
    ) {}

    public function getPaginatedTools(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->campaignToolRepository->paginate($filters, $perPage);
    }

    public function getPublishedTools(int $perPage = 12): LengthAwarePaginator
    {
        return $this->campaignToolRepository->published($perPage);
    }

    public function getPublishedNavTools(): Collection
    {
        return $this->campaignToolRepository->publishedForNav();
    }

    public function getPublicShowData(string $slug): CampaignTool
    {
        return $this->campaignToolRepository->findPublishedBySlug($slug);
    }

    public function createTool(array $data, ?UploadedFile $featuredImage = null): CampaignTool
    {
        $data = $this->prepareData($data);

        if ($featuredImage) {
            $data['featured_image'] = $featuredImage->store('campaign-tools', 'public');
        }

        return $this->campaignToolRepository->create($data);
    }

    public function updateTool(CampaignTool $campaignTool, array $data, ?UploadedFile $featuredImage = null): bool
    {
        $data = $this->prepareData($data, $campaignTool);

        if ($featuredImage) {
            if ($campaignTool->featured_image) {
                Storage::disk('public')->delete($campaignTool->featured_image);
            }

            $data['featured_image'] = $featuredImage->store('campaign-tools', 'public');
        }

        return $this->campaignToolRepository->update($campaignTool, $data);
    }

    public function deleteTool(CampaignTool $campaignTool): bool
    {
        if ($campaignTool->featured_image) {
            Storage::disk('public')->delete($campaignTool->featured_image);
        }

        return $this->campaignToolRepository->delete($campaignTool);
    }

    private function prepareData(array $data, ?CampaignTool $campaignTool = null): array
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['slug'] = Str::slug($data['slug'] ?: $data['title']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['nav_label'] = $data['nav_label'] ?: null;
        $data['excerpt'] = $data['excerpt'] ?: null;
        $data['meta_title'] = $data['meta_title'] ?: null;
        $data['meta_description'] = $data['meta_description'] ?: null;

        return $data;
    }
}