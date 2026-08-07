<?php

namespace App\Http\Controllers\Customer;

use App\Domain\Customer\UseCases\ListCustomersUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListCustomersController extends Controller
{
    public function __construct(
        private ListCustomersUseCase $useCase
    ) {}

    public function __invoke(Request $request)
    {
        $customers = $this->useCase->run(
            filters: $request->all(),
            perPage: $request->integer('per_page', 15)
        );

        return ApiResponse::success(CustomerResource::collection($customers));
    }
}
