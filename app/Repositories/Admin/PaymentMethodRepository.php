<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\PaymentMethodRepositoryInterface;
use App\Models\PaymentMethod;
use Illuminate\Support\Collection;

class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    public function all(): Collection
    {
        return PaymentMethod::orderBy('sort_order', 'asc')
                            ->orderBy('name', 'asc')
                            ->get();
    }

    public function getActive(): Collection
    {
        return PaymentMethod::where('is_active', true)
                            ->orderBy('sort_order', 'asc')
                            ->orderBy('name', 'asc')
                            ->get();
    }

    public function create(array $data): PaymentMethod
    {
        return PaymentMethod::create($data);
    }

    public function update(PaymentMethod $paymentMethod, array $data): bool
    {
        return $paymentMethod->update($data);
    }

    public function delete(PaymentMethod $paymentMethod): bool
    {
        return $paymentMethod->delete();
    }
}
