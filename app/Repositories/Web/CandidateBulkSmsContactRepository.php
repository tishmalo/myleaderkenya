<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\CandidateBulkSmsContactRepositoryInterface;
use App\Models\CandidateSupportContact;
use App\Models\SupportGroupType;
use Illuminate\Support\Collection;

class CandidateBulkSmsContactRepository implements CandidateBulkSmsContactRepositoryInterface
{
    public function activeClassifications(): Collection
    {
        return SupportGroupType::active()->ordered()->get();
    }

    public function countForCandidate(int $candidateId, ?int $classificationId = null): int
    {
        return $this->query($candidateId, $classificationId)->count();
    }

    public function countsByClassification(int $candidateId): Collection
    {
        return CandidateSupportContact::query()
            ->where('candidate_id', $candidateId)
            ->whereNotNull('phone')
            ->selectRaw('support_group_type_id, COUNT(*) as aggregate')
            ->groupBy('support_group_type_id')
            ->pluck('aggregate', 'support_group_type_id');
    }
    public function recipientsForCandidate(int $candidateId, ?int $classificationId = null): Collection
    {
        return $this->query($candidateId, $classificationId)->select(['id', 'phone'])->get();
    }

    private function query(int $candidateId, ?int $classificationId = null)
    {
        return CandidateSupportContact::query()
            ->where('candidate_id', $candidateId)
            ->whereNotNull('phone')
            ->when($classificationId, fn ($query) => $query->where('support_group_type_id', $classificationId));
    }
}