<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\PaymentMethod;
use Illuminate\Support\Collection;

interface PaymentMethodRepositoryInterface
{
    /**
     * Get all payment methods.
     */
    public function all(): Collection;

    /**
     * Get active payment methods.
     */
    public function getActive(): Collection;

    /**
     * Create a new payment method.
     */
    public function create(array $data): PaymentMethod;

    /**
     * Update an existing payment method.
     */
    public function update(PaymentMethod $paymentMethod, array $data): bool;

    /**
     * Delete a payment method.
     */
    public function delete(PaymentMethod $paymentMethod): bool;
}
