<?php

namespace App\Http\Controllers\Order;

use App\Domain\Order\UseCases\ListOrdersUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderListResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListOrdersController extends Controller
{
    public function __construct(private ListOrdersUseCase $useCase) {}

    public function __invoke(Request $request)
    {
        $orders = $this->useCase->run(
            filters: $request->all(),
            perPage: $request->integer('per_page', 15)
        );

        return ApiResponse::success(OrderListResource::collection($orders));
    }
}
