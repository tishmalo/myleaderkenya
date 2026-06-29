<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\LiveStatFigureRepositoryInterface;
use App\Models\LiveStatFigure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LiveStatFigureService
{
    public function __construct(
        private LiveStatFigureRepositoryInterface $liveStatFigureRepository
    ) {}

    public function getIndexData(int $perPage = 20): array
    {
        return [
            'figures' => $this->liveStatFigureRepository->paginate($perPage),
            'batches' => $this->liveStatFigureRepository->batches(),
            'metrics' => LiveStatFigure::METRICS,
        ];
    }

    public function generateBatch(array $data): int
    {
        $batchId = 'generated-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(6));
        $batchName = $data['batch_name'] ?: 'Generated live stats ' . now()->format('M d, Y H:i');
        $created = 0;

        foreach (LiveStatFigure::METRICS as $metricKey => $label) {
            $value = (int) ($data['figures'][$metricKey] ?? 0);

            if ($value <= 0) {
                continue;
            }

            $this->liveStatFigureRepository->create([
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

        return $created;
    }

    public function deleteFigure(LiveStatFigure $figure): bool
    {
        return $this->liveStatFigureRepository->delete($figure);
    }

    public function deleteBatch(string $batchId): int
    {
        return $this->liveStatFigureRepository->deleteBatch($batchId);
    }
}
