<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportPollingStationsRequest;
use App\Http\Requests\Admin\StorePollingStationRequest;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;

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
        ['messages' => $messages, 'groups' => $groups]
            = $this->dashboardService->getMessagesAndGroups(auth()->user());

        return view('messages.index', compact('messages', 'groups'));
    }

    public function stats()
    {
        [
            'totalVoters' => $totalVoters,
            'totalRegisteredVoters' => $totalRegisteredVoters,
            'voterStats' => $voterStats,
        ] = $this->dashboardService->getVoterStats();

        return view('voters.stats', compact('totalVoters', 'totalRegisteredVoters', 'voterStats'));
    }

    public function stations()
    {
        ['stations' => $stations, 'blocs' => $blocs]
            = $this->dashboardService->getStationsAndBlocs();

        return view('stations.index', compact('stations', 'blocs'));
    }

    public function storeStation(StorePollingStationRequest $request)
    {
        $this->dashboardService->createPollingStation($request->validated());

        return response()->json(['message' => 'Polling station added successfully']);
    }

    public function importStations(ImportPollingStationsRequest $request)
    {
        $imported = $this->dashboardService->importStations($request->validated('stations'));

        return response()->json([
            'message'  => 'Import successful',
            'imported' => $imported,
        ]);
    }

    public function getCountiesByBloc($blocId)
    {
        return response()->json(
            $this->dashboardService->getCountiesByBloc($blocId)
        );
    }

    public function getCounties($name)
    {
        return response()->json(
            $this->dashboardService->getCountiesByName($name)
        );
    }

    public function getConstituenciesByCounty(Request $request)
    {
        if (!$countyName = $request->query('county')) {
            return response()->json([]);
        }

        return response()->json(
            $this->dashboardService->getConstituenciesByCounty($countyName)
        );
    }

    public function getWardsByConstituency(Request $request)
    {
        if (!$constituencyName = $request->query('constituency')) {
            return response()->json([]);
        }

        return response()->json(
            $this->dashboardService->getWardsByConstituency($constituencyName)
        );
    }

    public function getPollingStations($type, $id)
    {
        return response()->json(
            $this->dashboardService->getPollingStationsFiltered($type, $id)
        );
    }

    public function getPollingStationsByWard(Request $request)
    {
        if (!$wardName = $request->query('ward')) {
            return response()->json([]);
        }

        return response()->json(
            $this->dashboardService->getPollingStationsByWard($wardName)
        );
    }

    public function tags()
    {
        $tags = $this->dashboardService->getTags();

        return view('dashboard.index', compact('tags'));
    }
}

