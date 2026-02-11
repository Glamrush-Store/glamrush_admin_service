<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\Actions;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class BuildProductQueryAction
{
    public function run(array $filters): Builder
    {
        return Product::query()
            ->when($filters['is_active'] ?? null, fn ($q, $v) => $q->where('is_active', $v)
            )
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v)
            )
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%")
            )
            ->orderBy(
                $filters['sort'] ?? 'sort_order',
                $filters['direction'] ?? 'asc'
            );
    }
}
