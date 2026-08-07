<?php

namespace App\Http\Controllers\Order;

use App\Domain\Order\UseCases\UpdateOrderStatusUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\Order\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Order;

class UpdateOrderStatusController extends Controller
{
    public function __construct(private UpdateOrderStatusUseCase $useCase) {}

    public function __invoke(Order $order, UpdateOrderStatusRequest $request)
    {
        $order = $this->useCase->run($order, $request->validated('status'));

        return ApiResponse::success(new OrderResource($order));
    }
}
