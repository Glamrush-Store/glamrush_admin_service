<?php

namespace App\Http\Controllers\Shipping\ShippingMethod;

use App\Domain\Shipping\ShippingMethod\UseCases\CreateShippingMethodUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shipping\CreateShippingMethodRequest;
use App\Http\Resources\Shipping\ShippingMethodResource;
use App\Http\Responses\ApiResponse;

class CreateShippingMethodController extends Controller
{
    public function __construct(private CreateShippingMethodUseCase $useCase) {}

    public function __invoke(CreateShippingMethodRequest $request)
    {
        $method = $this->useCase->run($request->validated());

        return ApiResponse::success(new ShippingMethodResource($method), 'Shipping method created', 201);
    }
}
