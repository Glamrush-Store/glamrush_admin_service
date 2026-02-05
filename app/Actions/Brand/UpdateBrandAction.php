<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */


namespace App\Actions\Brand;

use App\Models\Brand;

class UpdateBrandAction
{
    public function run(Brand $brand, array $data): Brand
    {
        $brand->update([
            'name' => $data['name'] ?? $brand->name,
            // 'slug' => $data['slug'] ?? $brand->slug,
            'is_active' => $data['is_active'] ?? $brand->is_active,
            'description' => $data['description'] ?? $brand->description,
            'meta_title' => $data['meta_title'] ?? $brand->meta_title,
            'meta_description' => $data['meta_description'] ?? $brand->meta_description,
            'meta_keywords' => $data['meta_keywords'] ?? $brand->meta_keywords,
            'sort_order' => $data['sort_order'] ?? $brand->sort_order,
        ]);

        return $brand;
    }
}
