<?php

namespace App\Domain\Shipping\ShippingMethod\UseCases;

use App\Models\ShippingMethod;

class DeleteShippingMethodUseCase
{
    public function run(ShippingMethod $method): void
    {
        $method->delete();
    }
}
