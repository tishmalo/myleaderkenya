<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(
        private LocationService $locationService
    ) {}

    public function getCounties(): JsonResponse
    {
        $counties = $this->locationService->getAllCounties();

        return response()->json($counties);
    }

    public function getConstituenciesByCounty(Request $request): JsonResponse
    {
        $constituencies = $this->locationService->getConstituenciesByCountyName(
            $request->query('county')
        );

        return response()->json($constituencies);
    }

    public function getWardsByConstituency(Request $request): JsonResponse
    {
        $wards = $this->locationService->getWardsByConstituencyName(
            $request->query('constituency')
        );

        return response()->json($wards);
    }
    /**
     * GET /api/get_locations (Public)
     */
    public function getLocations(): JsonResponse
    {
        $locations = $this->locationService->getAllLocations();

        return response()->json($locations);
    }

    /**
     * POST /api/upload_location (Protected – requires Bearer token)
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $this->locationService->uploadLocation(
            $request->user(),
            (float) $request->latitude,
            (float) $request->longitude
        );

        return response()->json([
            'message'  => 'Location updated successfully',
            'username' => $request->user()->username,
        ]);
    }
}

