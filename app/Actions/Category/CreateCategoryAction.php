<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Actions\Category;

use App\Models\Category;

class CreateCategoryAction
{
    public function run(array $data): Category
    {
        try {
            return Category::create($data);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to create category', 0, $e);
        }

    }
}
