<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */
namespace App\Domain\Product\Actions;


use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class UploadProductPhotosAction
{
    /**
     * @param UploadedFile[] $photos
     */
    public function run(Product $product, array $photos): void
    {
        foreach ($photos as $photo) {
            $product
                ->addMedia($photo)
                ->toMediaCollection('catalog-photos');
        }

        Log::info("product photo uploaded");
    }
}
