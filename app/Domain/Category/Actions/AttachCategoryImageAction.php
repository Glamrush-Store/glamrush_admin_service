<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Actions\Category;

namespace App\Domain\Category\Actions;

use App\Models\Category;
use Illuminate\Http\UploadedFile;

class AttachCategoryImageAction
{
    public function run(Category $category, UploadedFile $file): void
    {
        $category
            ->addMedia($file)
            ->toMediaCollection('catalog-photos');
    }
}
