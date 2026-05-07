<?php

namespace App\Http\Controllers\Shipping\Shipment;

use App\Domain\Shipping\Shipment\UseCases\DeleteShipmentUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Shipment;

class DeleteShipmentController extends Controller
{
    public function __construct(private DeleteShipmentUseCase $useCase) {}

    public function __invoke(Shipment $shipment)
    {
        $this->useCase->run($shipment);

        return ApiResponse::success(null, 'Shipment deleted', 204);
    }
}
