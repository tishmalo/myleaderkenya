<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\LiveStatFigureRepositoryInterface;
use App\Models\LiveStatFigure;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LiveStatFigureRepository implements LiveStatFigureRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return LiveStatFigure::query()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function batches(): Collection
    {
        return LiveStatFigure::query()
            ->select('batch_id', 'batch_name', 'source')
            ->selectRaw('COUNT(*) as figures_count')
            ->selectRaw('SUM(value) as total_value')
            ->selectRaw('MAX(created_at) as latest_created_at')
            ->whereNotNull('batch_id')
            ->groupBy('batch_id', 'batch_name', 'source')
            ->orderByDesc('latest_created_at')
            ->get();
    }

    public function activeTotals(): array
    {
        $defaults = array_fill_keys(array_keys(LiveStatFigure::METRICS), 0);

        return array_merge($defaults, LiveStatFigure::query()
            ->where('active', true)
            ->select('metric_key', DB::raw('SUM(value) as total'))
            ->groupBy('metric_key')
            ->pluck('total', 'metric_key')
            ->map(fn ($value) => (int) $value)
            ->all());
    }
    public function create(array $data): LiveStatFigure
    {
        return LiveStatFigure::create($data);
    }

    public function delete(LiveStatFigure $figure): bool
    {
        return $figure->delete();
    }

    public function deleteBatch(string $batchId): int
    {
        return LiveStatFigure::where('batch_id', $batchId)->delete();
    }
}

