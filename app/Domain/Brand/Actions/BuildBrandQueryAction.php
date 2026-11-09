<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Brand\Actions;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;

class BuildBrandQueryAction
{
    public function run(array $filters): Builder
    {
        $sortBy = $filters['sort_by'] ?? 'sort_order';
        $sortDirection = $filters['sort_dir'] ?? 'asc';

        $query = Brand::query();

        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', $filters['is_active']);
        }

        if (! empty($filters['search'])) {
            $search = mb_strtolower($filters['search']);

            $query->where(function (Builder $query) use ($search) {
                $pattern = "%{$search}%";

                $query->whereRaw('LOWER(name) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(code) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(slug) LIKE ?', [$pattern]);
            });
        }

        return $query
            ->orderBy("brands.{$sortBy}", $sortDirection)
            ->orderBy('brands.id', $sortDirection);
    }
}
