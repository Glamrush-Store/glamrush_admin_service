<?php

namespace App\Http\Controllers\Shipping\Shipment;

use App\Domain\Shipping\Shipment\UseCases\ListShipmentsUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shipping\ShipmentResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListShipmentsController extends Controller
{
    public function __construct(private ListShipmentsUseCase $useCase) {}

    public function __invoke(Request $request)
    {
        $shipments = $this->useCase->run(
            filters: $request->all(),
            perPage: $request->integer('per_page', 15)
        );

        return ApiResponse::success(ShipmentResource::collection($shipments));
    }
}
