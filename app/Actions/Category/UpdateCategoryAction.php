<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Actions\Category;

use App\Models\Category;

class UpdateCategoryAction
{
    public function run(Category $category, array $data): Category
    {
        $category->update([
            'name' => $data['name'] ?? $category->name,
            // 'slug' => $data['slug'] ?? $category->slug,
            'parent_id' => $data['parent_id'] ?? $category->parent_id,
            'is_active' => $data['is_active'] ?? $category->is_active,
            'description' => $data['description'] ?? $category->description,
            'meta_title' => $data['meta_title'] ?? $category->meta_title,
            'meta_description' => $data['meta_description'] ?? $category->meta_description,
            'meta_keywords' => $data['meta_keywords'] ?? $category->meta_keywords,
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
        ]);

        return $category;
    }
}
