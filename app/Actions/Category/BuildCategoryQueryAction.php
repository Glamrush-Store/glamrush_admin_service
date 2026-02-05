<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

class BuildCategoryQueryAction
{
    public function run(array $filters): Builder
    {
        return Category::query()
            ->when($filters['is_active'] ?? null, fn ($q, $v) => $q->where('is_active', $v)
            )
            ->when($filters['parent_id'] ?? null, fn ($q, $v) => $q->where('parent_id', $v)
            )
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%")
            )
            ->orderBy(
                $filters['sort'] ?? 'sort_order',
                $filters['direction'] ?? 'asc'
            );
    }
}
