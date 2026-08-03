<?php

namespace App\Contracts\Repositories\Audit;

use App\Models\Audit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditRepositoryInterface
{
    public function paginate(array $filters, ?int $candidateId = null): LengthAwarePaginator;
    public function findForCandidate(int $id, ?int $candidateId = null): ?Audit;
    public function create(array $attributes): Audit;
}
