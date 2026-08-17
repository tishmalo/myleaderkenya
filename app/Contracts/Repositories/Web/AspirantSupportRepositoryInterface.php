<?php

namespace App\Contracts\Repositories\Web;

use App\Models\AspirantSupportPayment;
use App\Models\User;
use Illuminate\Support\Collection;

interface AspirantSupportRepositoryInterface
{
    public function create(array $data): AspirantSupportPayment;
    public function findByReference(string $reference): ?AspirantSupportPayment;
    public function lockedByReference(string $reference): ?AspirantSupportPayment;
    public function update(AspirantSupportPayment $support, array $data): bool;
    public function forSupporter(User $user, int $limit = 30): Collection;
    public function forCandidate(int $candidateId, int $limit = 50): Collection;
    public function forCandidateAdmin(int $candidateId, int $limit = 100): Collection;
    public function adminTotalsForCandidate(int $candidateId): array;
    public function paidTotalForCandidate(int $candidateId): float;
    public function findForCandidate(int $supportId, int $candidateId): AspirantSupportPayment;
}
