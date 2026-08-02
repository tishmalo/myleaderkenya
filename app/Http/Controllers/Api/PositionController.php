<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 50), 100);

        $positions = Position::query()
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->ordered()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Position $position) => $this->formatPosition($position));

        return response()->json($positions);
    }

    private function formatPosition(Position $position): array
    {
        return [
            'id' => $position->id,
            'name' => $position->name,
            'description' => $position->description,
            'sort_order' => $position->sort_order,
        ];
    }
}
