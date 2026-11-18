<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\County;
use App\Services\SeoService;
use App\Services\Web\PublicCountyService;
use Illuminate\View\View;

class PublicCountyController extends Controller
{
    public function __construct(
        private PublicCountyService $counties,
        private SeoService $seo,
    ) {}

    public function index(): View
    {
        $this->seo->setTitle('Kenya Counties – 2027 Aspirants & Election Data | My Leader Kenya')
            ->setDescription('Explore all 47 Kenya counties. View 2027 aspirants, voter data, events and election information by county on My Leader Kenya.')
            ->setCanonical(route('counties.public'));

        return view('counties.public.index', $this->counties->directoryData());
    }

    public function show(County $county): View
    {

        $title = "{$county->name} County – 2027 Aspirants & Election Data | My Leader Kenya";
        if (strlen($title) > 60) {
            $title = "{$county->name} County 2027 Aspirants | My Leader Kenya";
        }

        $this->seo->setTitle($title)
            ->setDescription("View all 2027 aspirants, events, news and election information for {$county->name} County. Complete county-level data on My Leader Kenya.")
            ->setCanonical(route('county.show', $county));

        return view('counties.public.show', $this->counties->dataFor($county));
    }
}
