<?php

namespace App\Domain\PaymentMethod\UseCases;

use App\Models\PaymentMethod;

class UpdatePaymentMethodUseCase
{
    public function run(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        $paymentMethod->update($data);

        return $paymentMethod->refresh();
    }
}
