<?php

namespace App\Domain\Shipping\ShippingZone\UseCases;

use App\Models\ShippingZone;

class CreateShippingZoneUseCase
{
    public function run(array $data): ShippingZone
    {
        return ShippingZone::create($data);
    }
}
