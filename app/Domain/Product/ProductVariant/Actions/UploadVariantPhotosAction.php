<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\ProductVariant\Actions;

use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;

class UploadVariantPhotosAction
{
    /**
     * @param  UploadedFile[]  $photos
     */
    public function run(ProductVariant $variant, array $photos): void
    {
        foreach ($photos as $photo) {
            $variant
                ->addMedia($photo)
                ->toMediaCollection('catalog-photos');
        }
    }
}
