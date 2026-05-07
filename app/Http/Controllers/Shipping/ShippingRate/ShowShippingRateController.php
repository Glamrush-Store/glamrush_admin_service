<?php

namespace App\Http\Controllers\Shipping\ShippingRate;

use App\Domain\Shipping\ShippingRate\UseCases\ShowShippingRateUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shipping\ShippingRateResource;
use App\Http\Responses\ApiResponse;
use App\Models\ShippingRate;

class ShowShippingRateController extends Controller
{
    public function __construct(private ShowShippingRateUseCase $useCase) {}

    public function __invoke(ShippingRate $shippingRate)
    {
        $rate = $this->useCase->run($shippingRate);

        return ApiResponse::success(new ShippingRateResource($rate));
    }
}
