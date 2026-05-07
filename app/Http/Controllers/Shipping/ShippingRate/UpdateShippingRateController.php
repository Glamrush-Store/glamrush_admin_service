<?php

namespace App\Http\Controllers\Shipping\ShippingRate;

use App\Domain\Shipping\ShippingRate\UseCases\UpdateShippingRateUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shipping\UpdateShippingRateRequest;
use App\Http\Resources\Shipping\ShippingRateResource;
use App\Http\Responses\ApiResponse;
use App\Models\ShippingRate;

class UpdateShippingRateController extends Controller
{
    public function __construct(private UpdateShippingRateUseCase $useCase) {}

    public function __invoke(ShippingRate $shippingRate, UpdateShippingRateRequest $request)
    {
        $rate = $this->useCase->run($shippingRate, $request->validated());

        return ApiResponse::success(new ShippingRateResource($rate));
    }
}
