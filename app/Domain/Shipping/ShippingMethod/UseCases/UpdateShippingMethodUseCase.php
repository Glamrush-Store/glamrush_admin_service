<?php

namespace App\Domain\Shipping\ShippingMethod\UseCases;

use App\Models\ShippingMethod;

class UpdateShippingMethodUseCase
{
    public function run(ShippingMethod $method, array $data): ShippingMethod
    {
        $method->update($data);

        return $method->fresh();
    }
}
