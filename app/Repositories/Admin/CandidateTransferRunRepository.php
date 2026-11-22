<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CandidateTransferRunRepositoryInterface;
use App\Models\CandidateTransferRun;
use Illuminate\Support\Collection;

class CandidateTransferRunRepository implements CandidateTransferRunRepositoryInterface
{
    public function recent(int $limit = 10): Collection
    {
        return CandidateTransferRun::query()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function create(array $data): CandidateTransferRun
    {
        return CandidateTransferRun::create($data);
    }
}
