<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\StatsService;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function __construct(
        private StatsService $statsService
    ) {}

    public function totalUsers(): JsonResponse
    {
        return response()->json(
            $this->statsService->getTotalUsers()
        );
    }

    public function liveStats(): JsonResponse
    {
        return response()->json(
            $this->statsService->getLiveStats()
        );
    }
}
