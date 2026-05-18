<?php

namespace App\Domain\PaymentMethod\UseCases;

use App\Models\PaymentMethod;

class ShowPaymentMethodUseCase
{
    public function run(PaymentMethod $paymentMethod): PaymentMethod
    {
        return $paymentMethod;
    }
}
