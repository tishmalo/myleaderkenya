<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\County;
use App\Models\Bloc;
use Illuminate\Http\Request;

class CountyController extends Controller
{

//     public function index()
// {
//     $counties = County::with('bloc')
//         ->withCount('pollingStations')
//         ->withSum('pollingStations', 'registered_voters')
//         ->latest()
//         ->paginate(15);

//     return view('counties.index', compact('counties'));
// }

    public function index()
    {
        $counties = County::with('bloc')
            ->withCount('pollingStations')                    // Number of polling stations
            ->withSum('pollingStations', 'registered_voters') // Total registered voters
            ->latest()
            ->paginate(15);

        return view('counties.index', compact('counties'));
    }

    public function create()
    {
        $blocs = Bloc::orderBy('name')->get();
        return view('counties.create', compact('blocs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:counties',
            'bloc_id' => 'required|exists:blocs,id',
            'area' => 'nullable|string',
            'population' => 'nullable|integer|min:0',
            'capital' => 'nullable|string',
            'registered_voters' => 'nullable|integer|min:0',
            'postal_abbreviation' => 'nullable|string|max:10',
        ]);

        County::create($request->all());

        return redirect()->route('counties.index')
            ->with('success', 'County created successfully');
    }

    public function edit(County $county)
    {
        $blocs = Bloc::orderBy('name')->get();
        return view('counties.edit', compact('county', 'blocs'));
    }

    public function update(Request $request, County $county)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:counties,name,' . $county->id,
            'bloc_id' => 'required|exists:blocs,id',
            'area' => 'nullable|string',
            'population' => 'nullable|integer|min:0',
            'capital' => 'nullable|string',
            'registered_voters' => 'nullable|integer|min:0',
            'postal_abbreviation' => 'nullable|string|max:10',
        ]);

        $county->update($request->all());

        return redirect()->route('counties.index')
            ->with('success', 'County updated successfully');
    }

    public function destroy(County $county)
    {
        $county->delete();
        return redirect()->route('counties.index')
            ->with('success', 'County deleted successfully');
    }

    // Import (kept as is)
    public function import(Request $request)
    {
        $request->validate([
            'counties' => 'required|array',
            'counties.*.name' => 'required|string|max:255',
            'counties.*.bloc_id' => 'required|exists:blocs,id',
            'counties.*.area' => 'nullable|string',
            'counties.*.population' => 'nullable|integer',
            'counties.*.capital' => 'nullable|string',
            'counties.*.registered_voters' => 'nullable|integer',
            'counties.*.postal_abbreviation' => 'nullable|string|max:10',
        ]);

        $imported = 0;

        foreach ($request->counties as $countyData) {
            County::updateOrCreate(
                ['name' => $countyData['name']],
                [
                    'bloc_id' => $countyData['bloc_id'],
                    'area' => $countyData['area'] ?? null,
                    'population' => $countyData['population'] ?? null,
                    'capital' => $countyData['capital'] ?? null,
                    'registered_voters' => $countyData['registered_voters'] ?? null,
                    'postal_abbreviation' => $countyData['postal_abbreviation'] ?? null,
                ]
            );
            $imported++;
        }

        return response()->json([
            'message' => 'Counties imported successfully',
            'imported' => $imported
        ]);
    }
}