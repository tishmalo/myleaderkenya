<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Admin\SettingService;
use App\Services\Web\LandingService;

class FrontendPageController extends Controller
{
    public function __construct(
        private SettingService $settingService,
        private LandingService $landingService
    ) {}

    public function about()
    {
        return $this->show('about-us');
    }

    public function liveStats()
    {
        return $this->show('live-stats', $this->landingService->getLandingData());
    }

    public function downloadApp()
    {
        return $this->show('download-app');
    }

    public function contact()
    {
        return $this->show('contact-us');
    }

    private function show(string $page, array $extra = [])
    {
        $pageData = $this->settingService->getFrontendPage($page);

        return view('frontend-pages.show', array_merge(['pageData' => $pageData], $extra));
    }
}
