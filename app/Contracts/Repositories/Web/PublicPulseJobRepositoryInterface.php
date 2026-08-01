<?php

namespace App\Contracts\Repositories\Web;

use App\Models\PublicPulseJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PublicPulseJobRepositoryInterface
{
    public function create(array $attributes): PublicPulseJob;

    public function find(int $id): ?PublicPulseJob;

    public function findByJobRef(string $jobRef): ?PublicPulseJob;

    public function findByEngineJobId(string $engineJobId): ?PublicPulseJob;

    public function paginateForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function candidateOptions(): Collection;

    public function dueForSync(int $limit = 50): Collection;

    public function update(PublicPulseJob $job, array $attributes): PublicPulseJob;

    public function updateNonTerminal(PublicPulseJob $job, array $attributes): bool;
}