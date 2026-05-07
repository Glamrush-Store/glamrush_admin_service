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
    public function __construct(private CreateProductUseCase $useCase) {}

    public function __invoke(
        CreateProductRequest $request,
    ) {
        $product = $this->useCase->execute($request->all());

        return ApiResponse::success($product, 'OK', 201);
    }
}
