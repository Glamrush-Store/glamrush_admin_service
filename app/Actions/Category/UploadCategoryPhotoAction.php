<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Actions\Category;

class UploadCategoryPhotoAction
{
    public function run(UploadedFile $photo): string
    {
        return $photo->store('categories', 'public');
    }
}
