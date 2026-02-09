<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\ProductVariant;

use App\Domain\Product\ProductVariant\UseCases\DeleteProductVariantUseCase;
use App\Http\Requests\ProductVariant\DeleteProductVariantRequest;
use App\Models\ProductVariant;

class DeleteProductVariantController
{
    public function __invoke(
        DeleteProductVariantRequest $request,
        ProductVariant $variant,
        DeleteProductVariantUseCase $useCase
    ) {
        $useCase->execute($variant);

        return response()->json([
            'message' => 'Variant deleted successfully.',
        ]);
    }
}
