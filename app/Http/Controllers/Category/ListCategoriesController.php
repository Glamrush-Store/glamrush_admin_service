<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Category;

use App\Http\Responses\ApiResponse;
use App\Usecases\Category\ListCategoriesUseCase;
use Illuminate\Http\Request;

class ListCategoriesController
{
    public function __invoke(
        Request $request,
        ListCategoriesUseCase $useCase
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
