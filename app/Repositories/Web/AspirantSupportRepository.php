<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\AspirantSupportRepositoryInterface;
use App\Models\AspirantSupportPayment;
use App\Models\User;
use Illuminate\Support\Collection;

class AspirantSupportRepository implements AspirantSupportRepositoryInterface
{
    public function create(array $data): AspirantSupportPayment { return AspirantSupportPayment::create($data); }
    public function findByReference(string $reference): ?AspirantSupportPayment { return AspirantSupportPayment::where('checkout_reference', $reference)->first(); }
    public function lockedByReference(string $reference): ?AspirantSupportPayment { return AspirantSupportPayment::where('checkout_reference', $reference)->lockForUpdate()->first(); }
    public function update(AspirantSupportPayment $support, array $data): bool { return $support->update($data); }
    public function forSupporter(User $user, int $limit = 30): Collection
    {
        return AspirantSupportPayment::with('candidate:id,name')->where('user_id', $user->id)->latest()->limit($limit)->get();
    }
    public function forCandidate(int $candidateId, int $limit = 50): Collection
    {
        return AspirantSupportPayment::with('user:id,name')->where('candidate_id', $candidateId)->where('status', 'paid')->latest('paid_at')->limit($limit)->get();
    }
    public function forCandidateAdmin(int $candidateId, int $limit = 100): Collection
    {
        return AspirantSupportPayment::query()
            ->with(['user:id,name,email', 'package:id,name'])
            ->where('candidate_id', $candidateId)
            ->latest('id')
            ->limit($limit)
            ->get();
    }
    public function adminTotalsForCandidate(int $candidateId): array
    {
        $totals = AspirantSupportPayment::query()
            ->where('candidate_id', $candidateId)
            ->where('status', 'paid')
            ->selectRaw('COUNT(*) as paid_count')
            ->selectRaw('COALESCE(SUM(gross_amount), 0) as gross_total')
            ->selectRaw('COALESCE(SUM(platform_fee_amount), 0) as platform_fee_total')
            ->selectRaw('COALESCE(SUM(aspirant_amount), 0) as aspirant_total')
            ->first();

        return [
            'paid_count' => (int) $totals->paid_count,
            'gross_total' => (float) $totals->gross_total,
            'platform_fee_total' => (float) $totals->platform_fee_total,
            'aspirant_total' => (float) $totals->aspirant_total,
        ];
    }
    public function paidTotalForCandidate(int $candidateId): float
    {
        return (float) AspirantSupportPayment::where('candidate_id', $candidateId)->where('status', 'paid')->sum('aspirant_amount');
    }
    public function findForCandidate(int $supportId, int $candidateId): AspirantSupportPayment
    {
        return AspirantSupportPayment::whereKey($supportId)->where('candidate_id', $candidateId)->where('status', 'paid')->firstOrFail();
    }
}
