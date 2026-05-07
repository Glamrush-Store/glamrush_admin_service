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
        return Brand::query()
            ->when($filters['is_active'] ?? null, fn ($q, $v) => $q->where('is_active', $v)
            )
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%")
            )
            ->orderBy(
                $filters['sort'] ?? 'sort_order',
                $filters['direction'] ?? 'asc'
            );
    }
}
