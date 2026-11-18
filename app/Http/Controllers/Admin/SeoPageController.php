<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSeoPageRequest;
use App\Models\SeoPage;
use App\Services\SeoPageService;

class SeoPageController extends Controller
{
    public function __construct(
        private SeoPageService $seoPageService
    ) {}

    public function index()
    {
        return view('settings.seo-pages.index', [
            'pages' => $this->seoPageService->getPages(),
        ]);
    }

    public function edit(SeoPage $seoPage)
    {
        return view('settings.seo-pages.edit', compact('seoPage'));
    }

    public function update(UpdateSeoPageRequest $request, SeoPage $seoPage)
    {
        $this->seoPageService->updatePage($seoPage, $request->validated());

        return redirect()->route('seo-pages.index')
            ->with('success', 'SEO page updated successfully.');
    }
}
