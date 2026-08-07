<?php

namespace App\Domain\Shipping\ShippingZone\UseCases;

use App\Models\ShippingZone;

class DeleteShippingZoneUseCase
{
    public function run(ShippingZone $zone): void
    {
        $zone->delete();
    }
}
