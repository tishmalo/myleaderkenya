<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(private SitemapService $sitemap) {}

    public function index(): Response
    {
        return $this->xml($this->sitemap->index());
    }

    public function file(string $file): Response
    {
        $xml = $this->sitemap->file($file);

        abort_unless($xml !== null, 404);

        return $this->xml($xml);
    }

    private function xml(string $xml): Response
    {
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
