<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\ProductVariant;

use App\Domain\Product\ProductVariant\UseCases\UpdateProductVariantUseCase;
use App\Http\Requests\ProductVariant\UpdateProductVariantRequest;
use App\Http\Responses\ApiResponse;
use App\Models\ProductVariant;

class UpdateProductVariantController
{
    public function __construct(private UpdateProductVariantUseCase $useCase) {}

    public function __invoke(
        UpdateProductVariantRequest $request,
        ProductVariant $variant,
    ) {

        $productVariant = $this->useCase->execute($variant, $request->validated());

        return ApiResponse::success($productVariant);

    }
}
