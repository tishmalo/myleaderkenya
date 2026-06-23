<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\PaymentMethodRepositoryInterface;
use App\Models\PaymentMethod;
use Illuminate\Support\Collection;

class PaymentMethodService
{
    public function __construct(
        private PaymentMethodRepositoryInterface $paymentMethodRepository
    ) {}

    public function getAllPaymentMethods(): Collection
    {
        return $this->paymentMethodRepository->all();
    }

    public function getActivePaymentMethods(): Collection
    {
        return $this->paymentMethodRepository->getActive();
    }

    public function createPaymentMethod(array $data): PaymentMethod
    {
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : false;
        return $this->paymentMethodRepository->create($data);
    }

    public function updatePaymentMethod(PaymentMethod $paymentMethod, array $data): bool
    {
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : false;
        return $this->paymentMethodRepository->update($paymentMethod, $data);
    }

    public function deletePaymentMethod(PaymentMethod $paymentMethod): bool
    {
        return $this->paymentMethodRepository->delete($paymentMethod);
    }
}
