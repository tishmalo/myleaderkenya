<?php

namespace App\Contracts\Repositories\Audit;

use App\Models\Audit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AuditRepositoryInterface
{
    public function paginate(array $filters, ?int $candidateId = null): LengthAwarePaginator;
    public function candidateCreatorSummary(array $filters = []): Collection;
    public function findForCandidate(int $id, ?int $candidateId = null): ?Audit;
    public function create(array $attributes): Audit;
}
