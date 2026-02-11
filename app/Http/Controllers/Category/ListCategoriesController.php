<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Category;

use App\Domain\Category\UseCases\ListCategoriesUseCase;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListCategoriesController
{
    public function __construct(private ListCategoriesUseCase $useCase) {}

    public function __invoke(
        Request $request,
    ) {

        $categories = $this->useCase->run(
            filters: $request->all(),
            perPage: $request->integer('per_page', 5)
        );

        return ApiResponse::success($categories);
    }
}
