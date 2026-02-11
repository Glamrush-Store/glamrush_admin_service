<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Vendor\UseCases;

use App\Models\Vendor;

class ShowVendorUseCase
{
    public function run(Vendor $vendor): Vendor
    {
        return $vendor;
    }
}
