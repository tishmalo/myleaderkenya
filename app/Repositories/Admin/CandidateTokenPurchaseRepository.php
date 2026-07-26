<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CandidateTokenPurchaseRepositoryInterface;
use App\Models\CandidateTokenPurchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CandidateTokenPurchaseRepository implements CandidateTokenPurchaseRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return CandidateTokenPurchase::query()
            ->with(['candidate.position', 'user', 'paymentMethod'])
            ->when(! empty($filters['search']), function ($query) use ($filters): void {
                $query->where(function ($inner) use ($filters): void {
                    $inner->where('package_name', 'like', "%{$filters['search']}%")
                        ->orWhere('payment_reference', 'like', "%{$filters['search']}%")
                        ->orWhereHas('candidate', fn ($candidate) => $candidate->where('name', 'like', "%{$filters['search']}%"));
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): CandidateTokenPurchase
    {
        return CandidateTokenPurchase::create($data);
    }

    public function findByCheckoutReference(string $reference): ?CandidateTokenPurchase
    {
        return CandidateTokenPurchase::where('checkout_reference', $reference)->first();
    }

    public function lockByCheckoutReference(string $reference): ?CandidateTokenPurchase
    {
        return CandidateTokenPurchase::where('checkout_reference', $reference)->lockForUpdate()->first();
    }

    public function update(CandidateTokenPurchase $purchase, array $data): bool
    {
        return $purchase->update($data);
    }

}
