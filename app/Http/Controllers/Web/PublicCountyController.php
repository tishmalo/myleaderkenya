<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\County;
use App\Services\Web\PublicCountyService;
use Illuminate\View\View;

class PublicCountyController extends Controller
{
    public function __construct(private PublicCountyService $counties) {}

    public function show(County $county): View
    {
        return view('counties.public.show', $this->counties->dataFor($county));
    }
}
