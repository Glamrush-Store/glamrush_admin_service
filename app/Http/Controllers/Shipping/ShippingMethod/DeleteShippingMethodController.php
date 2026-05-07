<?php

namespace App\Http\Controllers\Shipping\ShippingMethod;

use App\Domain\Shipping\ShippingMethod\UseCases\DeleteShippingMethodUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ShippingMethod;

class DeleteShippingMethodController extends Controller
{
    public function __construct(private DeleteShippingMethodUseCase $useCase) {}

    public function __invoke(ShippingMethod $shippingMethod)
    {
        $this->useCase->run($shippingMethod);

        return ApiResponse::success(null, 'Shipping method deleted', 204);
    }
}
