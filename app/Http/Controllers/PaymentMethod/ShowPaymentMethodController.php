<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Domain\PaymentMethod\UseCases\ShowPaymentMethodUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethod\PaymentMethodResource;
use App\Http\Responses\ApiResponse;
use App\Models\PaymentMethod;

class ShowPaymentMethodController extends Controller
{
    public function __construct(private ShowPaymentMethodUseCase $useCase) {}

    public function __invoke(PaymentMethod $paymentMethod)
    {
        return ApiResponse::success(new PaymentMethodResource($this->useCase->run($paymentMethod)));
    }
}
