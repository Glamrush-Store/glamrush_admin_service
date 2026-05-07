<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */


namespace App\Domain\Product\ProductVariant\UseCases;

use App\Models\ProductVariant;

class ShowProductVariantUseCase
{
    public function run(ProductVariant $productVariant): ProductVariant
    {
        return $productVariant;
    }
}
