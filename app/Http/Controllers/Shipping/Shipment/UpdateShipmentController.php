<?php

namespace App\Http\Controllers\Shipping\Shipment;

use App\Domain\Shipping\Shipment\UseCases\UpdateShipmentUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shipping\UpdateShipmentRequest;
use App\Http\Resources\Shipping\ShipmentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Shipment;

class UpdateShipmentController extends Controller
{
    public function __construct(private UpdateShipmentUseCase $useCase) {}

    public function __invoke(Shipment $shipment, UpdateShipmentRequest $request)
    {
        $shipment = $this->useCase->run($shipment, $request->validated());

        return ApiResponse::success(new ShipmentResource($shipment));
    }
}
