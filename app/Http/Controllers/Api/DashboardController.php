<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ImportStationsRequest;
use App\Http\Requests\Api\StoreStationRequest;
use App\Services\Api\DashboardService;
use App\Services\Api\LocationService;
use App\Services\Api\PollingStationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private PollingStationService $pollingStationService,
        private LocationService $locationService
    ) {}

    public function index()
    {
        $data = $this->dashboardService->getDashboardData();

        return view('dashboard', $data);
    }

    public function messages()
    {
        $data = $this->dashboardService->getMessages(auth()->user());

        return view('messages.index', $data);
    }

    public function stats()
    {
        $data = $this->dashboardService->getStats();

        return view('voters.stats', $data);
    }

    public function stations()
    {
        $data = $this->dashboardService->getStations();

        return view('stations.index', $data);
    }

    public function storeStation(StoreStationRequest $request)
    {
        $this->pollingStationService->storeStation($request->validated());

        return response()->json(['message' => 'Polling station added successfully']);
    }

    public function importStations(ImportStationsRequest $request)
    {
        $result = $this->pollingStationService->importStations($request->stations);

        return response()->json([
            'message'  => 'Import successful',
            'imported' => $result['imported']
        ]);
    }

    public function getAllCounties()
    {
        $counties = $this->locationService->getAllCounties();

        return response()->json($counties);
    }

    public function getCountiesByBloc($blocId)
    {
        $counties = $this->locationService->getCountiesByBloc($blocId);

        return response()->json($counties);
    }

    public function getCountiesByName($name)
    {
        $counties = $this->locationService->getCountiesByName($name);

        return response()->json($counties);
    }

    public function getConstituenciesByCounty(Request $request)
    {
        $countyName = $request->query('county');
        $constituencies = $this->locationService->getConstituenciesByCountyName($countyName);

        return response()->json($constituencies);
    }

    public function getWardsByConstituency(Request $request)
    {
        $constituencyName = $request->query('constituency');
        $wards = $this->locationService->getWardsByConstituencyName($constituencyName);

        return response()->json($wards);
    }

    public function getPollingStations($type, $id)
    {
        $stations = $this->locationService->getPollingStationsByType($type, $id);

        return response()->json($stations);
    }

    public function getPollingStationsByWard(Request $request)
    {
        $wardName = $request->query('ward');
        $stations = $this->locationService->getPollingStationsByWardName($wardName);

        return response()->json($stations);
    }

    public function tags()
    {
        $tags = $this->locationService->getTags();

        return view('dashboard.index', compact('tags'));
    }
}
