<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Category;

use App\Domain\Category\UseCases\CreateCategoryUseCase;
use App\Http\Requests\Category\CategoryRequest;
use App\Http\Responses\ApiResponse;

class CreateCategoryController
{
    public function __construct(private CreateCategoryUseCase $useCase) {}

    public function __invoke(
        CategoryRequest $request,
    ) {

        $category = $this->useCase->run(
            data: $request->validated(),
            photo: $request->file('photo')
        );

        return ApiResponse::success($category, 'OK', 201);

    }
}
