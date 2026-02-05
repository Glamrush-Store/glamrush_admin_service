<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Category;

use App\Http\Requests\Category\CategoryRequest;
use App\Http\Responses\ApiResponse;
use App\Usecases\Category\CreateCategoryUseCase;

class CreateCategoryController
{
    public function __invoke(
        CategoryRequest $request,
        CreateCategoryUseCase $useCase
    ) {
        try {
            $category = $useCase->run(
                data: $request->validated(),
                photo: $request->file('photo')
            );

            return ApiResponse::success($category, 'OK', 201);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }
}
