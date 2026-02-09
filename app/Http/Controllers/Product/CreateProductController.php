<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Product;

use App\Domain\Product\UseCases\CreateProductUseCase;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Responses\ApiResponse;

class CreateProductController
{
    public function __invoke(
        CreateProductRequest $request,
        CreateProductUseCase $useCase
    ) {
        try {
            $result = $useCase->execute($request->validated());

            return ApiResponse::success($result, 'OK', 201);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }

    }
}
