<?php

namespace App\Http\Controllers\Shipping\Shipment;

use App\Domain\Shipping\Shipment\UseCases\CreateShipmentUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shipping\CreateShipmentRequest;
use App\Http\Resources\Shipping\ShipmentResource;
use App\Http\Responses\ApiResponse;

class CreateShipmentController extends Controller
{
    public function __construct(private CreateShipmentUseCase $useCase) {}

    public function __invoke(CreateShipmentRequest $request)
    {
        $shipment = $this->useCase->run($request->validated());

        return ApiResponse::success(new ShipmentResource($shipment), 'Shipment created', 201);
    }
}
