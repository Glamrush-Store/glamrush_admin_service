<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Product;

use App\Domain\Product\UseCases\ListProductsUseCase;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListProductsController
{
    public function __invoke(
        Request $request,
        ListProductsUseCase $useCase
    ) {
        try {
            $result = $useCase->run(
                filters: $request->all(),
                perPage: $request->integer('per_page', 5)
            );

            return ApiResponse::success($result);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }
}
