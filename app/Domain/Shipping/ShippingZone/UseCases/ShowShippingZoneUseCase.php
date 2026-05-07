<?php

namespace App\Domain\Shipping\ShippingZone\UseCases;

use App\Models\ShippingZone;

class ShowShippingZoneUseCase
{
    public function run(ShippingZone $zone): ShippingZone
    {
        return $zone->load('rates.method');
    }
}
