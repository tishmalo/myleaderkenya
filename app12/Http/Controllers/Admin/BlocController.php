<?php

namespace App\Http\Controllers\Admin;   // ← Keep Admin namespace

use App\Http\Controllers\Controller;
use App\Models\Bloc;
use Illuminate\Http\Request;



class BlocController extends Controller
{
    public function import(Request $request)
{
    $request->validate([
        'blocs' => 'required|array',
        'blocs.*.name' => 'required|string|max:255',
        'blocs.*.tribes' => 'nullable|array',
        'blocs.*.tribe_population' => 'nullable|integer',
        'blocs.*.voting_patterns' => 'nullable|array',
    ]);

    $imported = 0;

    foreach ($request->blocs as $blocData) {
        Bloc::updateOrCreate(
            ['name' => $blocData['name']],
            [
                'tribes' => $blocData['tribes'] ?? null,
                'tribe_population' => $blocData['tribe_population'] ?? null,
                'voting_patterns' => $blocData['voting_patterns'] ?? null,
            ]
        );
        $imported++;
    }

    return response()->json([
        'message' => 'Blocs imported successfully',
        'imported' => $imported
    ]);
}

//

public function index()
{
    $blocs = Bloc::withCount('counties')
                 ->latest()
                 ->paginate(15);   // ← Change get() to paginate()

    return view('blocs.index', compact('blocs'));
}
    // public function index()
    // {
    //     $blocs = Bloc::withCount('counties')->latest()->get();
    //     return view('blocs.index', compact('blocs'));
    // }

    public function create()
    {
        return view('blocs.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'name'              => 'required|string|max:255|unique:blocs',
        'tribes'            => 'nullable|string',                    // changed
        'tribe_population'  => 'nullable|integer|min:0',
        'voting_patterns'   => 'nullable|json',                      // important for JSON
    ]);

    // Convert tribes from comma-separated string to array
    $tribes = $request->tribes 
        ? array_map('trim', explode(',', $request->tribes)) 
        : null;

    // Convert voting_patterns from JSON string to array (if provided)
    $votingPatterns = $request->voting_patterns 
        ? json_decode($request->voting_patterns, true) 
        : null;

    Bloc::create([
        'name'             => $request->name,
        'tribes'           => $tribes,                    // now array
        'tribe_population' => $request->tribe_population,
        'voting_patterns'  => $votingPatterns,
    ]);

    return redirect()->route('blocs.index')
        ->with('success', 'Bloc created successfully');
}
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255|unique:blocs',
    //         'tribes' => 'nullable|array',
    //         'tribe_population' => 'nullable|integer|min:0',
    //         'voting_patterns' => 'nullable|array',
    //     ]);

    //     Bloc::create([
    //         'name' => $request->name,
    //         'tribes' => $request->tribes,
    //         'tribe_population' => $request->tribe_population,
    //         'voting_patterns' => $request->voting_patterns,
    //     ]);

    //     return redirect()->route('blocs.index')
    //         ->with('success', 'Bloc created successfully');
    // }

    public function edit(Bloc $bloc)
    {
        return view('blocs.edit', compact('bloc'));
    }

    public function update(Request $request, Bloc $bloc)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blocs,name,' . $bloc->id,
            'tribes' => 'nullable|array',
            'tribe_population' => 'nullable|integer|min:0',
            'voting_patterns' => 'nullable|array',
        ]);

        $bloc->update($request->only(['name', 'tribes', 'tribe_population', 'voting_patterns']));

        return redirect()->route('blocs.index')
            ->with('success', 'Bloc updated successfully');
    }

    public function destroy(Bloc $bloc)
    {
        $bloc->delete();
        return redirect()->route('blocs.index')
            ->with('success', 'Bloc deleted successfully');
    }
}