<?php

namespace App\Domain\Shipping\ShippingRate\UseCases;

use App\Models\ShippingRate;

class UpdateShippingRateUseCase
{
    public function run(ShippingRate $rate, array $data): ShippingRate
    {
        $rate->update($data);

        return $rate->fresh();
    }
}
