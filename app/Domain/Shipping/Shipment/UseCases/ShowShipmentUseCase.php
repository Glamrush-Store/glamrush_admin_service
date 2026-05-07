<?php

namespace App\Domain\Shipping\Shipment\UseCases;

use App\Models\Shipment;

class ShowShipmentUseCase
{
    public function run(Shipment $shipment): Shipment
    {
        return $shipment->load(['method', 'zone']);
    }
}
