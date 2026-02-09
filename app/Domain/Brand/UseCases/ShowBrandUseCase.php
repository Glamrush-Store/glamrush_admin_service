<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Brand\UseCases;

use App\Models\Brand;

class ShowBrandUseCase
{
    public function run(Brand $brand): Brand
    {
        return $brand;
    }
}
