<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\ProductVariant\Actions;

use App\Models\ProductVariant;

class UpdateProductVariantAction
{
    public function run(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->update($data);

        return $variant;
    }
}
