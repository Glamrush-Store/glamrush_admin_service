<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Domain\PaymentMethod\UseCases\DeletePaymentMethodUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\PaymentMethod;

class DeletePaymentMethodController extends Controller
{
    public function __construct(private DeletePaymentMethodUseCase $useCase) {}

    public function __invoke(PaymentMethod $paymentMethod)
    {
        $this->useCase->run($paymentMethod);

        return ApiResponse::success(null, 'Payment method deleted', 204);
    }
}
