<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\ProductVariant;

use App\Domain\Product\ProductVariant\UseCases\UpdateProductVariantUseCase;
use App\Http\Requests\ProductVariant\UpdateProductVariantRequest;
use App\Models\ProductVariant;

class UpdateProductVariantController
{
    public function __invoke(
        UpdateProductVariantRequest $request,
        ProductVariant $variant,
        UpdateProductVariantUseCase $useCase
    ) {
        return response()->json(
            $useCase->execute($variant, $request->validated())
        );
    }
}
