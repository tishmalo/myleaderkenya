<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LiveStatFigureStoreRequest;
use App\Models\LiveStatFigure;
use App\Services\Admin\LiveStatFigureService;

class LiveStatFigureController extends Controller
{
    public function __construct(
        private LiveStatFigureService $liveStatFigureService
    ) {}

    public function index()
    {
        return view('live-stat-figures.index', $this->liveStatFigureService->getIndexData());
    }

    public function store(LiveStatFigureStoreRequest $request)
    {
        $created = $this->liveStatFigureService->generateBatch($request->validated());

        return redirect()
            ->route('live-stat-figures.index')
            ->with($created ? 'success' : 'error', $created ? 'Generated live stat figures saved.' : 'Add at least one figure above zero.');
    }

    public function destroy(LiveStatFigure $liveStatFigure)
    {
        $this->liveStatFigureService->deleteFigure($liveStatFigure);

        return redirect()
            ->route('live-stat-figures.index')
            ->with('success', 'Live stat figure deleted.');
    }

    public function destroyBatch(string $batchId)
    {
        $deleted = $this->liveStatFigureService->deleteBatch($batchId);

        return redirect()
            ->route('live-stat-figures.index')
            ->with('success', "Deleted {$deleted} generated live stat figure(s).");
    }
}

