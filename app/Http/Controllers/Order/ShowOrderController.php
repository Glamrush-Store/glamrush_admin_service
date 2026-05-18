<?php

namespace App\Http\Controllers\Order;

use App\Domain\Order\UseCases\ShowOrderUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Order;

class ShowOrderController extends Controller
{
    public function __construct(private ShowOrderUseCase $useCase) {}

    public function __invoke(Order $order)
    {
        $order = $this->useCase->run($order);

        return ApiResponse::success(new OrderResource($order));
    }
}
