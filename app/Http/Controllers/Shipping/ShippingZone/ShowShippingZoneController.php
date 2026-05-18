<?php

namespace App\Http\Controllers\Shipping\ShippingZone;

use App\Domain\Shipping\ShippingZone\UseCases\ShowShippingZoneUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shipping\ShippingZoneResource;
use App\Http\Responses\ApiResponse;
use App\Models\ShippingZone;

class ShowShippingZoneController extends Controller
{
    public function __construct(private ShowShippingZoneUseCase $useCase) {}

    public function __invoke(ShippingZone $shippingZone)
    {
        $zone = $this->useCase->run($shippingZone);

        return ApiResponse::success(new ShippingZoneResource($zone));
    }
}
