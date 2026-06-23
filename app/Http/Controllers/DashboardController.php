<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\PollingStation; 

class DashboardController extends Controller
{
    public function index()
{
    return view('dashboard', [
        'totalUsers'   => User::count(),
        'totalMessages'=> Message::count(),
        'messages'     => Message::latest()->take(30)->get(),
        'stations'     => PollingStation::latest()->get(),
        'totalVoters'  => User::where('is_voter', true)->count(),
        'voterStats'   => [
            'confirmedVoters' => User::where('is_voter', true)->count(),
            'avgAge' => round(User::whereNotNull('age')->avg('age') ?? 0, 1),
            'byCounty' => User::select('county')
                ->selectRaw('COUNT(*) as count')
                ->where('is_voter', true)
                ->groupBy('county')
                ->get(),
        ],
    ]);
}


public function messages()
{
    $user = auth()->user();

    $messages = \App\Models\Message::latest()
                    ->take(50)
                    ->get();

    $groups = $user->groups()
                ->withCount('members')
                ->latest()
                ->get();

    return view('messages.index', compact('messages', 'groups'));
}


public function stats()
{
    $totalVoters = User::where('is_voter', true)->count();
    $voterStats = [
        'confirmedVoters' => $totalVoters,
        'avgAge' => round(User::whereNotNull('age')->avg('age') ?? 0, 1),
        'byCounty' => User::select('county')
            ->selectRaw('COUNT(*) as count')
            ->where('is_voter', true)
            ->groupBy('county')
            ->get(),
    ];

    return view('voters.stats', compact('totalVoters', 'voterStats'));
}



public function stations()
{
    $stations = PollingStation::with('bloc')->latest()->get();
    $blocs    = \App\Models\Bloc::orderBy('name')->get();

    return view('stations.index', compact('stations', 'blocs'));
}

// public function storeStation(Request $request)
// {
//     $request->validate([
//         'bloc_id'           => 'nullable|exists:blocs,id',
//         'county'            => 'required|string|max:100',
//         'constituency'      => 'required|string|max:100',
//         'ward'              => 'required|string|max:100',
//         'office'            => 'required|string|max:255',
//         'near_landmark'     => 'nullable|string|max:255',
//         'distance_to_office'=> 'nullable|integer|min:0',
//         'lat'               => 'required|numeric|between:-90,90',
//         'lon'               => 'required|numeric|between:-180,180',
//         'registered_voters' => 'nullable|integer|min:0',
//     ]);

//     PollingStation::create([
//         'bloc_id'            => $request->bloc_id,
//         'county'             => $request->county,
//         'constituency'       => $request->constituency,
//         'ward'               => $request->ward,
//         'office'             => $request->office,
//         'near_landmark'      => $request->near_landmark,
//         'distance_to_office' => $request->distance_to_office ?? 0,
//         'lat'                => $request->lat,
//         'lon'                => $request->lon,
//         'registered_voters'  => $request->registered_voters ?? 0,
//         'is_user_added'      => true,
//     ]);

//     return response()->json(['message' => 'Polling station added successfully']);
// }

public function storeStation(Request $request)
{
    $request->validate([
        'bloc_id'           => 'nullable|exists:blocs,id',
        'county'            => 'required|string|max:100',
        'constituency'      => 'required|string|max:100',
        'ward'              => 'required|string|max:100',
        'office'            => 'required|string|max:255',
        'near_landmark'     => 'nullable|string|max:255',
        'distance_to_office'=> 'nullable|integer|min:0',
        'lat'               => 'required|numeric|between:-90,90',
        'lon'               => 'required|numeric|between:-180,180',
        'registered_voters' => 'nullable|integer|min:0',
    ]);

    PollingStation::create([
        'bloc_id'            => $request->bloc_id,
        'county'             => $request->county,
        'constituency'       => $request->constituency,
        'ward'               => $request->ward,
        'office'             => $request->office,
        'near_landmark'      => $request->near_landmark,
        'distance_to_office' => $request->distance_to_office ?? 0,
        'lat'                => $request->lat,
        'lon'                => $request->lon,
        'registered_voters'  => $request->registered_voters ?? 0,
        'is_user_added'      => true,
    ]);

    return response()->json(['message' => 'Polling station added successfully']);
}

// Get Counties by Bloc
public function getCountiesByBloc($blocId)
{
    $counties = \App\Models\County::where('bloc_id', $blocId)
                ->orderBy('name')
                ->pluck('name');

    return response()->json($counties);
}
public function getCounties($name)
{
    $counties = \App\Models\County::where('name', $name)
                ->orderBy('name')
                ->pluck('name');

    return response()->json($counties);
}

// Get Constituencies by County Name → Use county_id
public function getConstituenciesByCounty(Request $request)
{
    $countyName = $request->query('county');

    if (!$countyName) {
        return response()->json([]);
    }

    $constituencies = \App\Models\Constituency::where('county_id', function($query) use ($countyName) {
                        $query->select('id')
                              ->from('counties')
                              ->where('name', $countyName)
                              ->limit(1);
                    })
                    ->orderBy('name')
                    ->pluck('name');

    return response()->json($constituencies);
}

// Get Wards by Constituency Name → Use constituency_id
public function getWardsByConstituency(Request $request)
{
    $constituencyName = $request->query('constituency');

    if (!$constituencyName) {
        return response()->json([]);
    }

    $wards = \App\Models\Ward::where('constituency_id', function($query) use ($constituencyName) {
                $query->select('id')
                      ->from('constituencies')
                      ->where('name', $constituencyName)
                      ->limit(1);
             })
             ->orderBy('name')
             ->pluck('name');

    return response()->json($wards);
}

public function importStations(Request $request)
{
    $request->validate([
        'stations' => 'required|array|min:1',
        'stations.*.county'       => 'required|string|max:100',
        'stations.*.constituency' => 'required|string|max:100',
        'stations.*.office'       => 'required|string|max:255',
        'stations.*.near_landmark'=> 'nullable|string|max:255',
        'stations.*.distance_to_office' => 'nullable|integer',
        'stations.*.lat'          => 'required|numeric|between:-90,90',
        'stations.*.lon'          => 'required|numeric|between:-180,180',
    ]);

    $importedCount = 0;

    foreach ($request->stations as $station) {
        PollingStation::create([
            'county'             => $station['county'],
            'constituency'       => $station['constituency'],
            'office'             => $station['office'],
            'near_landmark'      => $station['near_landmark'] ?? null,
            'distance_to_office' => $station['distance_to_office'] ?? 0,
            'lat'                => $station['lat'],
            'lon'                => $station['lon'],
            'is_user_added'      => false,
        ]);
        $importedCount++;
    }

    return response()->json([
        'message'  => 'Import successful',
        'imported' => $importedCount
    ]);
}

public function getPollingStations($type, $id)
{
    $query = PollingStation::query();

    if ($type === 'county') {
        $query->where('county', function($q) use ($id) {
            $q->select('name')->from('counties')->where('id', $id);
        });
    } elseif ($type === 'constituency') {
        $query->where('constituency', function($q) use ($id) {
            $q->select('name')->from('constituencies')->where('id', $id);
        });
    } elseif ($type === 'ward') {
        $query->where('ward', function($q) use ($id) {
            $q->select('name')->from('wards')->where('id', $id);
        });
    }

    $stations = $query->select('office', 'ward', 'registered_voters')
                      ->orderBy('office')
                      ->get();

    return response()->json($stations);
}


// Get Polling Stations by Ward Name
public function getPollingStationsByWard(Request $request)
{
    $wardName = $request->query('ward');

    if (!$wardName) {
        return response()->json([]);
    }

    $stations = PollingStation::where('ward', $wardName)
                ->orderBy('office')
                ->pluck('office');   // or 'name' if your column is different

    return response()->json($stations);
}

// public function tags()
// {
//     $tags = \App\Models\Tag::all();

//     return view('dashboard.tags', compact('tags'));
// }

public function tags()
{
    $tags = \App\Models\Tag::orderBy('name')->get();

    return view('dashboard.index', compact('tags'));
}

// public function donors()
// {
//     $totalDonors = \App\Models\Donor::count();
//     $totalAmount = \App\Models\Donor::where('status', 'completed')->sum('amount');

//     $recentDonors = \App\Models\Donor::latest()
//                         ->with('user')
//                         ->take(20)
//                         ->get();

//     $byPaymentMethod = \App\Models\Donor::select('payment_method')
//                         ->selectRaw('COUNT(*) as count, SUM(amount) as total_amount')
//                         ->where('status', 'completed')
//                         ->groupBy('payment_method')
//                         ->get();

//     return view('donors.index', compact(
//         'totalDonors',
//         'totalAmount',
//         'recentDonors',
//         'byPaymentMethod'
//     ));
// }

// public function edit(Donor $donor)
// {
//     return view('donors.edit', compact('donor'));
// }

// public function update(Request $request, Donor $donor)
// {
//     $request->validate([
//         'name'            => 'required|string|max:255',
//         'email'           => 'nullable|email|max:255',
//         'phone'           => 'nullable|string|max:20',
//         'payment_method'  => 'required|in:mpesa,bank_transfer,paypal,cash,other',
//         'amount'          => 'required|numeric|min:1',
//         'details'         => 'nullable|string',
//         'status'          => 'required|in:pending,completed,failed,refunded',
//     ]);

//     $donor->update($request->only([
//         'name', 'email', 'phone', 'payment_method', 'amount', 
//         'details', 'status'
//     ]));

//     // Handle payment_details separately (as JSON)
//     if ($request->has('payment_details')) {
//         $donor->update(['payment_details' => $request->payment_details]);
//     }

//     return redirect()->route('donors.index')
//                      ->with('success', 'Donor record updated successfully.');
// }

}