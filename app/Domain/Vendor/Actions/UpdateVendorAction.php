<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Vendor\Actions;

use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;

class UpdateVendorAction
{
    public function execute(Vendor $vendor, array $data): Vendor
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $vendor->fill($data);
        $vendor->save();

        return $vendor;
    }
}
