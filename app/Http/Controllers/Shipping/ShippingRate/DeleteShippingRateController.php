<?php

namespace App\Http\Controllers\Shipping\ShippingRate;

use App\Domain\Shipping\ShippingRate\UseCases\DeleteShippingRateUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ShippingRate;

class DeleteShippingRateController extends Controller
{
    public function __construct(private DeleteShippingRateUseCase $useCase) {}

    public function __invoke(ShippingRate $shippingRate)
    {
        $this->useCase->run($shippingRate);

        return ApiResponse::success(null, 'Shipping rate deleted', 204);
    }
}
