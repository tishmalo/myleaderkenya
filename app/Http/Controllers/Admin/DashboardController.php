<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\DashboardService;
use App\Http\Requests\Admin\StoreStationRequest;
use App\Http\Requests\Admin\ImportStationsRequest;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index()
    {
        return view('dashboard', $this->dashboardService->getDashboardStats());
    }

    public function messages()
    {
        $data = $this->dashboardService->getMessagesAndGroups(auth()->user());

        return view('messages.index', [
            'messages' => $data['messages'],
            'groups'   => $data['groups']
        ]);
    }

    public function stats()
    {
        $stats = $this->dashboardService->getVoterStats();
        
        return view('voters.stats', [
            'totalVoters' => $stats['totalVoters'], 
            'voterStats'  => $stats['voterStats']
        ]);
    }

    public function stations()
    {
        $data = $this->dashboardService->getStationsAndBlocs();

        return view('stations.index', [
            'stations' => $data['stations'],
            'blocs'    => $data['blocs']
        ]);
    }

    public function storeStation(StoreStationRequest $request)
    {
        $this->dashboardService->createPollingStation($request->validated());

        return response()->json(['message' => 'Polling station added successfully']);
    }

    // Get Counties by Bloc
    public function getCountiesByBloc($blocId)
    {
        return response()->json($this->dashboardService->getCountiesByBloc($blocId));
    }

    public function getCounties($name)
    {
        return response()->json($this->dashboardService->getCountiesByName($name));
    }

    // Get Constituencies by County Name → Use county_id
    public function getConstituenciesByCounty(Request $request)
    {
        $countyName = $request->query('county');

        if (!$countyName) {
            return response()->json([]);
        }

        return response()->json($this->dashboardService->getConstituenciesByCounty($countyName));
    }

    // Get Wards by Constituency Name → Use constituency_id
    public function getWardsByConstituency(Request $request)
    {
        $constituencyName = $request->query('constituency');

        if (!$constituencyName) {
            return response()->json([]);
        }

        return response()->json($this->dashboardService->getWardsByConstituency($constituencyName));
    }

    public function importStations(ImportStationsRequest $request)
    {
        $importedCount = $this->dashboardService->importStations($request->stations);

        return response()->json([
            'message'  => 'Import successful',
            'imported' => $importedCount
        ]);
    }

    public function getPollingStations($type, $id)
    {
        return response()->json($this->dashboardService->getPollingStationsFiltered($type, $id));
    }

    // Get Polling Stations by Ward Name
    public function getPollingStationsByWard(Request $request)
    {
        $wardName = $request->query('ward');

        if (!$wardName) {
            return response()->json([]);
        }

        return response()->json($this->dashboardService->getPollingStationsByWard($wardName));
    }

    public function tags()
    {
        $tags = $this->dashboardService->getTags();

        return view('dashboard.index', compact('tags'));
    }
}