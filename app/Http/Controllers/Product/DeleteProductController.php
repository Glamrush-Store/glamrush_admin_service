<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Product;

use App\Domain\Product\UseCases\DeleteProductUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Product;

class DeleteProductController extends Controller
{
    public function __construct(private DeleteProductUseCase $useCase) {}

    public function __invoke(
        Product $product,
    ) {
        $this->useCase->run($product);

        return ApiResponse::success([], '', 204);

    }
}
