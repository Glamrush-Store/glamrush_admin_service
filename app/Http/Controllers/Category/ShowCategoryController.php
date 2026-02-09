<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Category;

use App\Domain\Category\UseCases\ShowCategoryUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Category;

class ShowCategoryController extends Controller
{
    public function __invoke(
        Category $category,
        ShowCategoryUseCase $useCase
    ) {
        try {
            $category = $useCase->run($category);

            return ApiResponse::success($category, 'OK', 200);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }

    }
}
