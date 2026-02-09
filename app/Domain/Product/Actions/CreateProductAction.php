<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\Actions;

use App\Models\Product;

class CreateProductAction
{
    public function run(array $data): Product
    {
        return Product::create($data);
    }
}
