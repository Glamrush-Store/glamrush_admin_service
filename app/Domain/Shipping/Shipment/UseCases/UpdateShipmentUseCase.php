<?php

namespace App\Domain\Shipping\Shipment\UseCases;

use App\Models\Shipment;

class UpdateShipmentUseCase
{
    public function run(Shipment $shipment, array $data): Shipment
    {
        $shipment->update($data);

        return $shipment->fresh();
    }
}
