<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Product;

use App\Domain\Category\UseCases\ShowCategoryUseCase;
use App\Domain\Product\UseCases\ShowProductUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use App\Models\Product;

class ShowProductController extends Controller
{
    public function __invoke(
        Product $product,
        ShowProductUseCase $useCase
    ) {
        try {
            $result = $useCase->run($product);
            return ApiResponse::success($result, 'OK', 200);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }

    }
}
