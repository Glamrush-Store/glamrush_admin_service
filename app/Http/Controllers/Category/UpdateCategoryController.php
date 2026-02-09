<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Category;

use App\Domain\Category\UseCases\UpdateCategoryUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateCategoryController extends Controller
{
    public function __invoke(
        Category $category,
        Request $request,
        UpdateCategoryUseCase $useCase
    ): JsonResponse {
        try {
            $category = $useCase->run(
                category: $category,
                data: $request->all(),
                photo: $request->file('image')
            );

            return ApiResponse::success($category, 'OK', 200);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }

    }
}
