<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\CandidateTokenTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CandidateTokenTransactionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function create(array $data): CandidateTokenTransaction;

    public function update(CandidateTokenTransaction $transaction, array $data): bool;
}
