<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\LandingService;

class LandingController extends Controller
{
    public function __construct(
        protected LandingService $landingService
    ) {}

    public function index()
    {
        $landingData = $this->landingService->getLandingData();

        return view('landing', $landingData);
    }
}