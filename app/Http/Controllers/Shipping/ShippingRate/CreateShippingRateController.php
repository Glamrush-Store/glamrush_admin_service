<?php

namespace App\Http\Controllers\Shipping\ShippingRate;

use App\Domain\Shipping\ShippingRate\UseCases\CreateShippingRateUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shipping\CreateShippingRateRequest;
use App\Http\Resources\Shipping\ShippingRateResource;
use App\Http\Responses\ApiResponse;

class CreateShippingRateController extends Controller
{
    public function __construct(private CreateShippingRateUseCase $useCase) {}

    public function __invoke(CreateShippingRateRequest $request)
    {
        $rate = $this->useCase->run($request->validated());

        return ApiResponse::success(new ShippingRateResource($rate), 'Shipping rate created', 201);
    }
}
