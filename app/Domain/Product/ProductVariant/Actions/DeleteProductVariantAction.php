<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\ProductVariant\Actions;

use App\Models\ProductVariant;

class DeleteProductVariantAction
{
    public function run(ProductVariant $variant): void
    {
        $variant->delete();
    }
}
