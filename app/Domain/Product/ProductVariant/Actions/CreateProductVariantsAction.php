<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\ProductVariant\Actions;

use App\Models\Product;
use App\Models\ProductVariant;

class CreateProductVariantsAction
{
    public function run(Product $product, array $variantData): ProductVariant
    {
        /** @var ProductVariant $variant */
        $variant = $product->variants()->create(
            collect($variantData)->except('photos')->toArray()
        );

        return $variant;
    }
}
