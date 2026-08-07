<?php

namespace App\Http\Controllers\Shipping\ShippingRate;

use App\Domain\Shipping\ShippingRate\UseCases\ListShippingRatesUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shipping\ShippingRateResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListShippingRatesController extends Controller
{
    public function __construct(private ListShippingRatesUseCase $useCase) {}

    public function __invoke(Request $request)
    {
        $rates = $this->useCase->run(
            filters: $request->all(),
            perPage: $request->integer('per_page', 15)
        );

        return ApiResponse::success(ShippingRateResource::collection($rates));
    }
}
