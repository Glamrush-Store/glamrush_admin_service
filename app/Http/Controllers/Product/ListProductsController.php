<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Product;

use App\Domain\Product\UseCases\ListProductsUseCase;
use App\Http\Resources\Product\ProductListResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListProductsController
{
    public function __construct(private ListProductsUseCase $useCase) {}

    public function __invoke(
        Request $request,
    ) {

        $products = $this->useCase->run(
            filters: $request->all(),
            perPage: $request->integer('per_page', 5)
        );

        return ApiResponse::success(ProductListResource::collection($products));

    }
}
