<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Brand\Actions;

use App\Models\Brand;
use Illuminate\Http\UploadedFile;

class AttachBrandImageAction
{
    public function run(Brand $brand, UploadedFile $file): void
    {
        $brand
            ->addMedia($file)
            ->toMediaCollection('image');
    }
}
