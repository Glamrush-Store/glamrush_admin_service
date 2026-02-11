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
    public function __construct(private DeleteCategoryUseCase $useCase) {}

    public function __invoke(
        Category $category,
    ): JsonResponse {
        $this->useCase->run($category);

        return ApiResponse::success([], '', 204);
    }
}
