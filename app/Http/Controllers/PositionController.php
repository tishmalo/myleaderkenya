<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::latest()->paginate(15);
        return view('positions.index', compact('positions'));
    }

    public function create()
    {
        return view('positions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:positions,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Position::create($request->only(['name', 'description']));

        return redirect()->route('positions.index')
                         ->with('success', 'Position created successfully!');
    }

    public function edit(Position $position)
    {
        return view('positions.edit', compact('position'));
    }

    public function update(Request $request, Position $position)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:positions,name,' . $position->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $position->update($request->only(['name', 'description']));

        return redirect()->route('positions.index')
                         ->with('success', 'Position updated successfully!');
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return response()->json([
            'success' => true,
            'message' => 'Position deleted successfully.'
        ]);
    }
}