<?php

namespace App\Domain\Shipping\ShippingRate\UseCases;

use App\Models\ShippingRate;

class CreateShippingRateUseCase
{
    public function run(array $data): ShippingRate
    {
        return ShippingRate::create($data);
    }
}
