<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\CandidateSmsBalanceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CandidateSmsBalanceRequestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): CandidateSmsBalanceRequest;

    public function update(CandidateSmsBalanceRequest $request, array $data): bool;
}
