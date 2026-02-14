<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Product;

use App\Domain\Product\UseCases\ShowProductUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Http\Responses\ApiResponse;
use App\Models\Product;

class ShowProductController extends Controller
{
    public function __construct(private ShowProductUseCase $useCase) {}

    public function __invoke(
        Product $product,
    ) {

        $product = $this->useCase->run($product);

        return ApiResponse::success(new ProductResource($product));
    }
}
