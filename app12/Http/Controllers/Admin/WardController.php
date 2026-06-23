<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ward;
use App\Models\Constituency;
use Illuminate\Http\Request;

class WardController extends Controller
{
    public function index()
    {
        $wards = Ward::with('constituency.county')
            ->withCount('pollingStations')
            ->withSum('pollingStations', 'registered_voters')
            ->latest()
            ->paginate(15);

        return view('wards.index', compact('wards'));
    }

    public function create()
    {
        $constituencies = Constituency::with('county')->orderBy('name')->get();
        return view('wards.create', compact('constituencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'constituency_id' => 'required|exists:constituencies,id',
            'population' => 'nullable|integer|min:0',
            'registered_voters' => 'nullable|integer|min:0',
        ]);

        Ward::create($request->all());

        return redirect()->route('wards.index')
            ->with('success', 'Ward created successfully');
    }

    public function edit(Ward $ward)
    {
        $constituencies = Constituency::with('county')->orderBy('name')->get();
        return view('wards.edit', compact('ward', 'constituencies'));
    }

    public function update(Request $request, Ward $ward)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'constituency_id' => 'required|exists:constituencies,id',
            'population' => 'nullable|integer|min:0',
            'registered_voters' => 'nullable|integer|min:0',
        ]);

        $ward->update($request->all());

        return redirect()->route('wards.index')
            ->with('success', 'Ward updated successfully');
    }

    public function destroy(Ward $ward)
    {
        $ward->delete();
        return redirect()->route('wards.index')
            ->with('success', 'Ward deleted successfully');
    }

    public function import(Request $request)
    {
        $request->validate([
            'wards' => 'required|array',
            'wards.*.name' => 'required|string|max:255',
            'wards.*.constituency_id' => 'required|exists:constituencies,id',
            'wards.*.population' => 'nullable|integer',
            'wards.*.registered_voters' => 'nullable|integer',
        ]);

        $imported = 0;

        foreach ($request->wards as $data) {
            Ward::updateOrCreate(
                ['name' => $data['name'], 'constituency_id' => $data['constituency_id']],
                [
                    'population' => $data['population'] ?? null,
                    'registered_voters' => $data['registered_voters'] ?? null,
                ]
            );
            $imported++;
        }

        return response()->json([
            'message' => 'Wards imported successfully',
            'imported' => $imported
        ]);
    }
}