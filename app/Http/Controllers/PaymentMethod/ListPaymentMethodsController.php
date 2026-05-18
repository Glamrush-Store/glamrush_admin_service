<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Domain\PaymentMethod\UseCases\ListPaymentMethodsUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethod\PaymentMethodResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListPaymentMethodsController extends Controller
{
    public function __construct(private ListPaymentMethodsUseCase $useCase) {}

    public function __invoke(Request $request)
    {
        $methods = $this->useCase->run(
            filters: $request->all(),
            perPage: $request->integer('per_page', 15)
        );

        return ApiResponse::success(PaymentMethodResource::collection($methods));
    }
}
