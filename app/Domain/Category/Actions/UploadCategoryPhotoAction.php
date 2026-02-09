<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Category\Actions;

use Illuminate\Http\UploadedFile;

class UploadCategoryPhotoAction
{
    public function run(UploadedFile $photo): string
    {
        return $photo->store('categories', 'public');
    }
}
