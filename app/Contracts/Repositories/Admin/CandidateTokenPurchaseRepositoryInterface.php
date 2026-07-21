<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\CandidateTokenPurchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CandidateTokenPurchaseRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): CandidateTokenPurchase;
}
