<?php

namespace App\Http\Controllers\Shipping\ShippingZone;

use App\Domain\Shipping\ShippingZone\UseCases\ListShippingZonesUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shipping\ShippingZoneResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListShippingZonesController extends Controller
{
    public function __construct(private ListShippingZonesUseCase $useCase) {}

    public function __invoke(Request $request)
    {
        $zones = $this->useCase->run(
            filters: $request->all(),
            perPage: $request->integer('per_page', 15)
        );

        return ApiResponse::success(ShippingZoneResource::collection($zones));
    }
}
