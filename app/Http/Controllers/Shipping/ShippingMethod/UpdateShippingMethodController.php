<?php

namespace App\Http\Controllers\Shipping\ShippingMethod;

use App\Domain\Shipping\ShippingMethod\UseCases\UpdateShippingMethodUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shipping\UpdateShippingMethodRequest;
use App\Http\Resources\Shipping\ShippingMethodResource;
use App\Http\Responses\ApiResponse;
use App\Models\ShippingMethod;

class UpdateShippingMethodController extends Controller
{
    public function __construct(private UpdateShippingMethodUseCase $useCase) {}

    public function __invoke(ShippingMethod $shippingMethod, UpdateShippingMethodRequest $request)
    {
        $method = $this->useCase->run($shippingMethod, $request->validated());

        return ApiResponse::success(new ShippingMethodResource($method));
    }
}
