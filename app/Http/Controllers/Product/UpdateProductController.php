<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Product;

use App\Domain\Product\UseCases\UpdateProductUseCase;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use Illuminate\Http\Request;

class UpdateProductController
{
    public function __construct(private UpdateProductUseCase $useCase) {}

    public function __invoke(
        Request $request,
        Product $product,
    ) {

        $result = $this->useCase->execute($product, $request->all());

        return ApiResponse::success($result);

    }
}
