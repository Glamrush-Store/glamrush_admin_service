<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Actions\Brand;

use App\Models\Brand;
use Illuminate\Support\Facades\Log;

class CreateBrandAction
{
    public function run(array $data): Brand
    {
            return Brand::create($data);
    }
}
