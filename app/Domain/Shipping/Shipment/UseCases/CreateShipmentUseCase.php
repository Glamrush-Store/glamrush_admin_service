<?php

namespace App\Domain\Shipping\Shipment\UseCases;

use App\Models\Shipment;

class CreateShipmentUseCase
{
    public function run(array $data): Shipment
    {
        return Shipment::create($data);
    }
}
