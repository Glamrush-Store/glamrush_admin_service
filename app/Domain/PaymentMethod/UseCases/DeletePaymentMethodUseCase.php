<?php

namespace App\Domain\PaymentMethod\UseCases;

use App\Models\PaymentMethod;

class DeletePaymentMethodUseCase
{
    public function run(PaymentMethod $paymentMethod): void
    {
        $paymentMethod->delete();
    }
}
