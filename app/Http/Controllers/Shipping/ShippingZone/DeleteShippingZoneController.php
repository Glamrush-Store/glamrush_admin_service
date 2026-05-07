<?php

namespace App\Http\Controllers\Shipping\ShippingZone;

use App\Domain\Shipping\ShippingZone\UseCases\DeleteShippingZoneUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ShippingZone;

class DeleteShippingZoneController extends Controller
{
    public function __construct(private DeleteShippingZoneUseCase $useCase) {}

    public function __invoke(ShippingZone $shippingZone)
    {
        $this->useCase->run($shippingZone);

        return ApiResponse::success(null, 'Shipping zone deleted', 204);
    }
}
