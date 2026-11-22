<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SeoService;
use App\Support\CoordinatorsGuide;
use Illuminate\View\View;

class CoordinatorsGuideController extends Controller
{
    public function __construct(
        private SeoService $seo,
    ) {}

    public function index(): View
    {
        $this->seo->setTitle('Coordinators Guide | My Leader Kenya')
            ->setDescription('How My Leader Kenya coordinators work: the roles, the tools and what a good day of coordination looks like.')
            ->setCanonical(route('coordinators-guide.public'));

        $rendered = CoordinatorsGuide::render(CoordinatorsGuide::document());

        return view('coordinators-guide', [
            'html' => $rendered['html'],
            'headings' => $rendered['headings'],
        ]);
    }
}
