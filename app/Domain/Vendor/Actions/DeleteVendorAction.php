<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Vendor\Actions;

use App\Models\Vendor;

class DeleteVendorAction
{
    public function execute(Vendor $vendor): void
    {
        $vendor->delete();
    }
}
