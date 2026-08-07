<?php

namespace App\Domain\Shipping\ShippingMethod\UseCases;

use App\Models\ShippingMethod;

class CreateShippingMethodUseCase
{
    public function run(array $data): ShippingMethod
    {
        return ShippingMethod::create($data);
    }
}
