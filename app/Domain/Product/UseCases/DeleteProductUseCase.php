<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\UseCases;

// use App\Domain\Category\Events\ProductDeletedEvent;
use App\Domain\Product\Events\ProductDeletedEvent;
use App\Models\Product;

class DeleteProductUseCase
{
    public function run(Product $product): void
    {
        $product->delete();
        event(new ProductDeletedEvent($product));
        // event(new ProductDeletedEvent($product));
    }
}
