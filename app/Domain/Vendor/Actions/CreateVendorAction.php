<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Vendor\Actions;

use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;

class CreateVendorAction
{
    public function execute(array $data): Vendor
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return Vendor::create($data);
    }
}
