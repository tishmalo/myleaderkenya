<?php

namespace App\Services\Web;

use App\Models\CampaignTool;
use App\Services\Admin\CampaignToolService;
use App\Services\Admin\SettingService;
use App\Services\SeoPageService;
use App\Services\SeoService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CampaignToolPublicService
{
    private const INDEX_ROUTE = 'campaign-tools.public';

    public function __construct(
        private CampaignToolService $campaignToolService,
        private SettingService $settingService,
        private SeoPageService $seoPageService,
        private SeoService $seoService
    ) {}

    /**
     * Everything the public campaign tools directory needs.
     */
    public function indexData(): array
    {
        $this->seoPageService->applyTo($this->seoService, self::INDEX_ROUTE);

        return [
            'campaignTools' => $this->campaignToolService->getPublishedTools(12),
            'requestCampaignTools' => $this->campaignToolService->getRequestToolOptions(),
            'recaptchaSiteKey' => $this->settingService->recaptchaSiteKey(),
        ];
    }

    /**
     * Everything the public campaign tool page needs.
     */
    public function showData(string $slug): array
    {
        $campaignTool = $this->campaignToolService->getPublicShowData($slug);

        $this->applyToolSeo($campaignTool);

        return [
            'campaignTool' => $campaignTool,
            'requestCampaignTools' => $this->campaignToolService->getRequestToolOptions(),
            'recaptchaSiteKey' => $this->settingService->recaptchaSiteKey(),
        ];
    }

    private function applyToolSeo(CampaignTool $campaignTool): void
    {
        $title = $campaignTool->meta_title
            ?: $campaignTool->title.' | Kenya 2027 Campaign Tool';

        if (! $campaignTool->meta_title && mb_strlen($title) > 60) {
            $title = Str::limit($campaignTool->title, 45, '').' | 2027';
        }

        $description = $campaignTool->meta_description
            ?: $campaignTool->excerpt
            ?: Str::limit(strip_tags((string) $campaignTool->content), 155)
            ?: 'Campaign tool for Kenya 2027 aspirants on My Leader Kenya.';

        $this->seoService
            ->setTitle($title)
            ->setDescription($description)
            ->setCanonical(route('campaign-tools.show', $campaignTool->slug))
            ->setOg([
                'title' => $title,
                'image' => $campaignTool->featured_image
                    ? Storage::url($campaignTool->featured_image)
                    : asset('images/myleader.png'),
            ]);
    }
}
