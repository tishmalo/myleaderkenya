<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\CandidateTokenPurchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CandidateTokenPurchaseRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function paginateKittyPurchases(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function paginateAspirantDonations(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): CandidateTokenPurchase;

    public function findByCheckoutReference(string $reference): ?CandidateTokenPurchase;

    public function lockByCheckoutReference(string $reference): ?CandidateTokenPurchase;

    public function update(CandidateTokenPurchase $purchase, array $data): bool;
}
