<?php

namespace App\Domain\PaymentMethod\UseCases;

use App\Models\PaymentMethod;

class CreatePaymentMethodUseCase
{
    public function run(array $data): PaymentMethod
    {
        return PaymentMethod::create($data);
    }
}
