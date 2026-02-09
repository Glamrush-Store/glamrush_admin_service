<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Brand\Actions;

use App\Models\Brand;

class CreateBrandAction
{
    public function run(array $data): Brand
    {
        return Brand::create($data);
    }
}
