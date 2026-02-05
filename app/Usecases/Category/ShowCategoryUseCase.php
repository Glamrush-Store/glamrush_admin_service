<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Usecases\Category;

use App\Models\Category;

class ShowCategoryUseCase
{
    public function run(Category $category): Category
    {
        return $category;
    }
}
