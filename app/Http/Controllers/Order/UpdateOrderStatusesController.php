<?php

namespace App\Http\Controllers\Order;

use App\Domain\Order\UseCases\UpdateOrderStatusesUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderStatusesRequest;
use App\Http\Resources\Order\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Order;

class UpdateOrderStatusesController extends Controller
{
    public function __construct(private UpdateOrderStatusesUseCase $useCase) {}

    public function __invoke(Order $order, UpdateOrderStatusesRequest $request)
    {
        $order = $this->useCase->run($order, $request->validated());

        return ApiResponse::success(new OrderResource($order));
    }
}
