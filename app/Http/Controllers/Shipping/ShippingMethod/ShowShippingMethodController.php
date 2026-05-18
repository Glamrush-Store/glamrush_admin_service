<?php

namespace App\Http\Controllers\Shipping\ShippingMethod;

use App\Domain\Shipping\ShippingMethod\UseCases\ShowShippingMethodUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shipping\ShippingMethodResource;
use App\Http\Responses\ApiResponse;
use App\Models\ShippingMethod;

class ShowShippingMethodController extends Controller
{
    public function __construct(private ShowShippingMethodUseCase $useCase) {}

    public function __invoke(ShippingMethod $shippingMethod)
    {
        $method = $this->useCase->run($shippingMethod);

        return ApiResponse::success(new ShippingMethodResource($method));
    }
}
