<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CandidateTokenPurchaseRepositoryInterface;
use App\Models\CandidateTokenPurchase;
use App\Models\AspirantSupportPayment;
use App\Models\UserTokenPurchase;
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

    public function paginateKittyPurchases(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return UserTokenPurchase::query()
            ->with(['user:id,name,email', 'kittyType:id,name'])
            ->when(! empty($filters['search']), function ($query) use ($filters): void {
                $search = $filters['search'];
                $query->where(function ($inner) use ($search): void {
                    $inner->where('package_name', 'like', "%{$search}%")
                        ->orWhere('checkout_reference', 'like', "%{$search}%")
                        ->orWhere('payment_reference', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage, ['*'], 'kitty_page')
            ->withQueryString();
    }

    public function paginateAspirantDonations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return AspirantSupportPayment::query()
            ->with(['user:id,name,email', 'candidate:id,name', 'package:id,name'])
            ->when(! empty($filters['search']), function ($query) use ($filters): void {
                $search = $filters['search'];
                $query->where(function ($inner) use ($search): void {
                    $inner->where('supporter_name', 'like', "%{$search}%")
                        ->orWhere('supporter_email', 'like', "%{$search}%")
                        ->orWhere('checkout_reference', 'like', "%{$search}%")
                        ->orWhere('payment_reference', 'like', "%{$search}%")
                        ->orWhereHas('candidate', fn ($candidate) => $candidate->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage, ['*'], 'donation_page')
            ->withQueryString();
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
