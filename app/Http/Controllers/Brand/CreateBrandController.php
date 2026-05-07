<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Brand;

use App\Domain\Brand\UseCases\CreateBrandUseCase;
use App\Http\Requests\Brand\CreateBrandRequest;
use App\Http\Responses\ApiResponse;

class CreateBrandController
{
    public function __construct(private CreateBrandUseCase $useCase) {}

    public function __invoke(
        CreateBrandRequest $request,
    ) {

        $brand = $this->useCase->run(
            data: $request->all(),
            photo: $request->file('photo')
        );

        return ApiResponse::success($brand, 'OK', 201);

    }
}
