<?php

namespace App\Contracts\Repositories\Web;

use Illuminate\Support\Collection;

interface CandidateBulkSmsContactRepositoryInterface
{
    public function activeClassifications(): Collection;

    public function countForCandidate(int $candidateId, ?int $classificationId = null): int;

    public function countsByClassification(int $candidateId): Collection;

    public function recipientsForCandidate(int $candidateId, ?int $classificationId = null): Collection;
}