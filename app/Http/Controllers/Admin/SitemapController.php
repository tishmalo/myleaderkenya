<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Web\SitemapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SitemapController extends Controller
{
    public function __construct(private SitemapService $sitemap) {}

    public function index(): View
    {
        return view('admin.sitemap.index', [
            'files' => $this->sitemap->files(),
            'totalUrls' => $this->sitemap->totalUrls(),
            'indexUrl' => route('sitemap.xml'),
        ]);
    }

    public function regenerate(): RedirectResponse
    {
        $this->sitemap->regenerate();

        return redirect()->route('sitemap.admin')
            ->with('success', 'Sitemap regenerated. Search engines will pick up the fresh files on their next crawl.');
    }
}
