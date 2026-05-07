<?php

namespace App\Http\Controllers\Shipping\ShippingZone;

use App\Domain\Shipping\ShippingZone\UseCases\CreateShippingZoneUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shipping\CreateShippingZoneRequest;
use App\Http\Resources\Shipping\ShippingZoneResource;
use App\Http\Responses\ApiResponse;

class CreateShippingZoneController extends Controller
{
    public function __construct(private CreateShippingZoneUseCase $useCase) {}

    public function __invoke(CreateShippingZoneRequest $request)
    {
        $zone = $this->useCase->run($request->validated());

        return ApiResponse::success(new ShippingZoneResource($zone), 'Shipping zone created', 201);
    }
}
