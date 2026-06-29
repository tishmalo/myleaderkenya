<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveStatFigure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LiveStatFigureController extends Controller
{
    public function index()
    {
        $figures = LiveStatFigure::query()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $batches = LiveStatFigure::query()
            ->select('batch_id', 'batch_name', 'source')
            ->selectRaw('COUNT(*) as figures_count')
            ->selectRaw('SUM(value) as total_value')
            ->selectRaw('MAX(created_at) as latest_created_at')
            ->whereNotNull('batch_id')
            ->groupBy('batch_id', 'batch_name', 'source')
            ->orderByDesc('latest_created_at')
            ->get();

        return view('live-stat-figures.index', [
            'figures' => $figures,
            'batches' => $batches,
            'metrics' => LiveStatFigure::METRICS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'batch_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'figures' => ['required', 'array'],
            'figures.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $batchId = 'generated-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(6));
        $batchName = $data['batch_name'] ?: 'Generated live stats ' . now()->format('M d, Y H:i');
        $created = 0;

        foreach (LiveStatFigure::METRICS as $metricKey => $label) {
            $value = (int) ($data['figures'][$metricKey] ?? 0);

            if ($value <= 0) {
                continue;
            }

            LiveStatFigure::create([
                'metric_key' => $metricKey,
                'label' => $label,
                'value' => $value,
                'source' => 'generated',
                'batch_id' => $batchId,
                'batch_name' => $batchName,
                'notes' => $data['notes'] ?? null,
                'active' => true,
            ]);

            $created++;
        }

        return redirect()
            ->route('live-stat-figures.index')
            ->with($created ? 'success' : 'error', $created ? 'Generated live stat figures saved.' : 'Add at least one figure above zero.');
    }

    public function destroy(LiveStatFigure $liveStatFigure)
    {
        $liveStatFigure->delete();

        return redirect()
            ->route('live-stat-figures.index')
            ->with('success', 'Live stat figure deleted.');
    }

    public function destroyBatch(string $batchId)
    {
        $deleted = LiveStatFigure::where('batch_id', $batchId)->delete();

        return redirect()
            ->route('live-stat-figures.index')
            ->with('success', "Deleted {$deleted} generated live stat figure(s).");
    }
}


