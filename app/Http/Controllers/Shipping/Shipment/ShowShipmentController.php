<?php

namespace App\Http\Controllers\Shipping\Shipment;

use App\Domain\Shipping\Shipment\UseCases\ShowShipmentUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shipping\ShipmentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Shipment;

class ShowShipmentController extends Controller
{
    public function __construct(private ShowShipmentUseCase $useCase) {}

    public function __invoke(Shipment $shipment)
    {
        $shipment = $this->useCase->run($shipment);

        return ApiResponse::success(new ShipmentResource($shipment));
    }
}
