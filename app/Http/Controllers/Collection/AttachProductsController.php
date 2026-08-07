<?php

namespace App\Http\Controllers\Collection;

use App\Domain\Collection\UseCases\AttachProductsUseCase;
use App\Http\Requests\Collection\AttachProductsRequest;
use App\Http\Resources\Collection\CollectionResource;
use App\Http\Responses\ApiResponse;
use App\Models\Collection;

class AttachProductsController
{
    public function __construct(private AttachProductsUseCase $useCase) {}

    public function __invoke(Collection $collection, AttachProductsRequest $request)
    {
        $collection = $this->useCase->run(
            collection: $collection,
            products: $request->validated('products')
        );

        return ApiResponse::success(new CollectionResource($collection));
    }
}
