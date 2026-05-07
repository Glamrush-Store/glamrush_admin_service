<?php

namespace App\Domain\Shipping\ShippingRate\UseCases;

use App\Models\ShippingRate;

class ShowShippingRateUseCase
{
    public function run(ShippingRate $rate): ShippingRate
    {
        return $rate->load(['zone', 'method']);
    }
}
