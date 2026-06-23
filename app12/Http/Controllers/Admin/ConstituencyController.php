<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Constituency;
use App\Models\County;
use Illuminate\Http\Request;

class ConstituencyController extends Controller
{
    public function index()
    {
        $constituencies = Constituency::with('county.bloc')
            ->withCount('pollingStations')
            ->withSum('pollingStations', 'registered_voters')
            ->latest()
            ->paginate(15);

        return view('constituencies.index', compact('constituencies'));
    }

    public function create()
    {
        $counties = County::orderBy('name')->get();
        return view('constituencies.create', compact('counties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'county_id' => 'required|exists:counties,id',
            'population' => 'nullable|integer|min:0',
            'number_of_seats' => 'required|integer|min:1',
            'registered_voters' => 'nullable|integer|min:0',     
            'position_name' => 'nullable|string',
        ]);

        Constituency::create($request->all());

        return redirect()->route('constituencies.index')
            ->with('success', 'Constituency created successfully');
    }

    public function edit(Constituency $constituency)
    {
        $counties = County::orderBy('name')->get();
        return view('constituencies.edit', compact('constituency', 'counties'));
    }

    public function update(Request $request, Constituency $constituency)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'county_id' => 'required|exists:counties,id',
            'population' => 'nullable|integer|min:0',
            'number_of_seats' => 'required|integer|min:1',
            'registered_voters' => 'nullable|integer|min:0', 
            'position_name' => 'nullable|string',
        ]);

        $constituency->update($request->all());

        return redirect()->route('constituencies.index')
            ->with('success', 'Constituency updated successfully');
    }

    public function destroy(Constituency $constituency)
    {
        $constituency->delete();
        return redirect()->route('constituencies.index')
            ->with('success', 'Constituency deleted successfully');
    }

    public function import(Request $request)
    {
        $request->validate([
            'constituencies' => 'required|array',
            'constituencies.*.name' => 'required|string|max:255',
            'constituencies.*.county_id' => 'required|exists:counties,id',
            'constituencies.*.population' => 'nullable|integer',
            'constituencies.*.number_of_seats' => 'nullable|integer',
            'constituencies.*.registered_voters' => 'nullable|integer',
            'constituencies.*.position_name' => 'nullable|string',
        ]);

        $imported = 0;

        foreach ($request->constituencies as $data) {
            Constituency::updateOrCreate(
                ['name' => $data['name'], 'county_id' => $data['county_id']],
                [
                    'population' => $data['population'] ?? null,
                    'number_of_seats' => $data['number_of_seats'] ?? 1,
                    'registered_voters' => $data['registered_voters'] ?? null,
                    'position_name' => $data['position_name'] ?? null,
                ]
            );
            $imported++;
        }

        return response()->json([
            'message' => 'Constituencies imported successfully',
            'imported' => $imported
        ]);
    }
}