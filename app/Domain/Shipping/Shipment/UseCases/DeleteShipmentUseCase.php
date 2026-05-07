<?php

namespace App\Domain\Shipping\Shipment\UseCases;

use App\Models\Shipment;

class DeleteShipmentUseCase
{
    public function run(Shipment $shipment): void
    {
        $shipment->delete();
    }
}
