<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\ProductVariant;

use App\Domain\Product\ProductVariant\UseCases\DeleteProductVariantUseCase;
use App\Http\Requests\ProductVariant\DeleteProductVariantRequest;
use App\Http\Responses\ApiResponse;
use App\Models\ProductVariant;

class DeleteProductVariantController
{
    public function __construct(
        private DeleteProductVariantUseCase $useCase
    ) {}

    public function __invoke(
        DeleteProductVariantRequest $request,
        ProductVariant $variant,
    ) {
        $this->useCase->execute($variant);
        ApiResponse::success([], '', 204);
    }
}
