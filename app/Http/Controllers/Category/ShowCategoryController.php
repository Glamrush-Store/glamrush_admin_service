<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Category;

use App\Domain\Category\UseCases\ShowCategoryUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;

class ShowCategoryController extends Controller
{
    public function __construct(private ShowCategoryUseCase $useCase) {}

    public function __invoke(
        Category $category,
    ) {

        $category = $this->useCase->run($category);

        return ApiResponse::success(new CategoryResource($category));

    }
}
