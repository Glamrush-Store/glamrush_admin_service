<?php

namespace App\Domain\Shipping\ShippingMethod\UseCases;

use App\Models\ShippingMethod;

class ShowShippingMethodUseCase
{
    public function run(ShippingMethod $method): ShippingMethod
    {
        return $method->load('rates.zone');
    }
}
