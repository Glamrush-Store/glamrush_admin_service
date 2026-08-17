<?php

/*
 * (c) 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\UseCases;

use App\Models\Product;

class ShowProductUseCase
{
    public function run(Product $product): Product
    {
        return $product->load([
            'variants',
            'categories',
            'primaryCategory',
            'brand',
            'vendor',
            'media',
            'variants.media',
            'collections',
        ]);
    }
}
