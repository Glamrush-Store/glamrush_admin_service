<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Domain\PaymentMethod\UseCases\CreatePaymentMethodUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethod\CreatePaymentMethodRequest;
use App\Http\Resources\PaymentMethod\PaymentMethodResource;
use App\Http\Responses\ApiResponse;

class CreatePaymentMethodController extends Controller
{
    public function __construct(private CreatePaymentMethodUseCase $useCase) {}

    public function __invoke(CreatePaymentMethodRequest $request)
    {
        $method = $this->useCase->run($request->validated());

        return ApiResponse::success(new PaymentMethodResource($method), 'Payment method created', 201);
    }
}
