<?php

namespace App\Domain\Shipping\ShippingRate\UseCases;

use App\Models\ShippingRate;

class DeleteShippingRateUseCase
{
    public function run(ShippingRate $rate): void
    {
        $rate->delete();
    }
}
