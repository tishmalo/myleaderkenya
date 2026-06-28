<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateFrontendPageRequest;
use App\Services\Admin\SettingService;

class FrontendPageController extends Controller
{
    public function __construct(private SettingService $settingService) {}

    public function index()
    {
        $pages = collect($this->settingService->getFrontendPageDefinitions())
            ->map(fn ($definition, $key) => $this->settingService->getFrontendPage($key));

        return view('settings.frontend-pages.index', compact('pages'));
    }

    public function edit(string $page)
    {
        $pageData = $this->settingService->getFrontendPage($page);

        return view('settings.frontend-pages.edit', compact('pageData'));
    }

    public function update(UpdateFrontendPageRequest $request, string $page)
    {
        $this->settingService->updateFrontendPage($page, $request->validated());

        return redirect()->route('frontend-pages.index')->with('success', 'Frontend page updated successfully.');
    }
}
