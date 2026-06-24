<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PositionStoreRequest;
use App\Http\Requests\Admin\PositionUpdateRequest;
use App\Models\Position;
use App\Services\Admin\PositionService;

class PositionController extends Controller
{
    public function __construct(
        private PositionService $positionService
    ) {}

    public function index()
    {
        $positions = $this->positionService->getPaginatedPositions();
        return view('positions.index', compact('positions'));
    }

    public function create()
    {
        return view('positions.create');
    }

    public function store(PositionStoreRequest $request)
    {
        $this->positionService->createPosition($request->validated());

        return redirect()->route('positions.index')
                         ->with('success', 'Position created successfully!');
    }

    public function edit(Position $position)
    {
        return view('positions.edit', compact('position'));
    }

    public function update(PositionUpdateRequest $request, Position $position)
    {
        $this->positionService->updatePosition($position, $request->validated());

        return redirect()->route('positions.index')
                         ->with('success', 'Position updated successfully!');
    }

    public function destroy(Position $position)
    {
        $this->positionService->deletePosition($position);

        return response()->json([
            'success' => true,
            'message' => 'Position deleted successfully.'
        ]);
    }
}
