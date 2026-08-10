<?php

namespace App\Http\Controllers\Order;

use App\Domain\Order\UseCases\CreateManualOrderUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateManualOrderRequest;
use App\Http\Resources\Order\OrderResource;
use App\Http\Responses\ApiResponse;

class CreateManualOrderController extends Controller
{
    public function __construct(private CreateManualOrderUseCase $useCase) {}

    public function __invoke(CreateManualOrderRequest $request)
    {
        $result = $this->useCase->run($request->validated(), $request->user());

        return ApiResponse::success(
            new OrderResource($result['order']),
            $result['replayed'] ? 'Manual order already recorded' : 'Manual order created',
            $result['replayed'] ? 200 : 201,
        );
    }
}
