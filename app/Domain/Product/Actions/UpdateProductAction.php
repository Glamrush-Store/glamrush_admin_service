<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\Actions;

use App\Models\Product;

class UpdateProductAction
{
    public function run(Product $product, array $data): bool
    {
        return $product->update($data);
    }
}
