<?php

namespace App\Http\Controllers\Shipping\ShippingMethod;

use App\Domain\Shipping\ShippingMethod\UseCases\ListShippingMethodsUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shipping\ShippingMethodResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListShippingMethodsController extends Controller
{
    public function __construct(private ListShippingMethodsUseCase $useCase) {}

    public function __invoke(Request $request)
    {
        $methods = $this->useCase->run(
            filters: $request->all(),
            perPage: $request->integer('per_page', 15)
        );

        return ApiResponse::success(ShippingMethodResource::collection($methods));
    }
}
