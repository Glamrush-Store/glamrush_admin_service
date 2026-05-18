<?php

namespace App\Http\Controllers\Shipping\ShippingZone;

use App\Domain\Shipping\ShippingZone\UseCases\UpdateShippingZoneUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shipping\UpdateShippingZoneRequest;
use App\Http\Resources\Shipping\ShippingZoneResource;
use App\Http\Responses\ApiResponse;
use App\Models\ShippingZone;

class UpdateShippingZoneController extends Controller
{
    public function __construct(private UpdateShippingZoneUseCase $useCase) {}

    public function __invoke(ShippingZone $shippingZone, UpdateShippingZoneRequest $request)
    {
        $zone = $this->useCase->run($shippingZone, $request->validated());

        return ApiResponse::success(new ShippingZoneResource($zone));
    }
}
