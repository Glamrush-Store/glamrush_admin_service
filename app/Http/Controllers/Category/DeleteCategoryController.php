<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Category;

use App\Domain\Category\UseCases\DeleteCategoryUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class DeleteCategoryController extends Controller
{
    public function __invoke(
        Category $category,
        DeleteCategoryUseCase $useCase
    ): JsonResponse {
        try {
            $useCase->run($category);

            return ApiResponse::success([], 'OK', 204);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }
}
