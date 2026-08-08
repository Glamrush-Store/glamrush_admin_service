<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Category;

use App\Domain\Category\UseCases\UpdateCategoryUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class UpdateCategoryController extends Controller
{
    public function __construct(private UpdateCategoryUseCase $useCase) {}

    public function __invoke(
        Category $category,
        UpdateCategoryRequest $request,
    ): JsonResponse {

        $category = $this->useCase->run(
            category: $category,
            data: $request->all(),
            photo: $request->file('photo')
        );

        return ApiResponse::success($category, 'Category Updated', 200);

    }
}
