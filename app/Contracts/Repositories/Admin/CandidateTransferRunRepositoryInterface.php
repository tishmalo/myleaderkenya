<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\CandidateTransferRun;
use Illuminate\Support\Collection;

interface CandidateTransferRunRepositoryInterface
{
    public function recent(int $limit = 10): Collection;

    public function create(array $data): CandidateTransferRun;
}
