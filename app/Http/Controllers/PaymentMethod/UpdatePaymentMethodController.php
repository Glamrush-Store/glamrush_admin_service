<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Domain\PaymentMethod\UseCases\UpdatePaymentMethodUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethod\UpdatePaymentMethodRequest;
use App\Http\Resources\PaymentMethod\PaymentMethodResource;
use App\Http\Responses\ApiResponse;
use App\Models\PaymentMethod;

class UpdatePaymentMethodController extends Controller
{
    public function __construct(private UpdatePaymentMethodUseCase $useCase) {}

    public function __invoke(PaymentMethod $paymentMethod, UpdatePaymentMethodRequest $request)
    {
        $method = $this->useCase->run($paymentMethod, $request->validated());

        return ApiResponse::success(new PaymentMethodResource($method));
    }
}
