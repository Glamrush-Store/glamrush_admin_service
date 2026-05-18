<?php

namespace App\Domain\Shipping\ShippingZone\UseCases;

use App\Models\ShippingZone;

class UpdateShippingZoneUseCase
{
    public function run(ShippingZone $zone, array $data): ShippingZone
    {
        $zone->update($data);

        return $zone->fresh();
    }
}
