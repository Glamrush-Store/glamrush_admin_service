<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Brand;

use App\Http\Requests\Brand\CreateBrandRequest;
use App\Http\Responses\ApiResponse;
use App\Usecases\Brand\CreateBrandUseCase;

class CreateBrandController
{
    public function __invoke(
        CreateBrandRequest $request,
        CreateBrandUseCase $useCase
    ) {
        try {
            $brand = $useCase->run(
                data: $request->validated(),
                photo: $request->file('photo')
            );

            return ApiResponse::success($brand, 'OK', 201);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }
}
