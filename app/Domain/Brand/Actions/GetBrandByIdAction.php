<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Brand\Actions;

use App\Models\Brand;

class GetBrandByIdAction
{
    public function run(string $brandId): Brand
    {
        return Brand::where('id', $brandId)->firstOrFail();
    }
}
