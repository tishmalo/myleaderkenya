<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\PollingStationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollingStationController extends Controller
{
    public function __construct(
        private PollingStationService $pollingStationService
    ) {}

    public function getPollingStations(string $type, string $id): JsonResponse
    {
        $stations = $this->pollingStationService->getPollingStationsFiltered($type, $id);

        return response()->json($stations);
    }
}
