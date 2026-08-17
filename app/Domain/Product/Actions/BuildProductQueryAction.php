<?php

/*
 * (c) 2026 Demilade Oyewusi
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
        $query = Product::query()
            ->select('products.*')
            ->with([
                'categories:id,name,slug',
                'primaryCategory:id,name,slug',
                'brand:id,name,slug',
                'vendor:id,business_name',
            ]);

        if (! empty($filters['category_id'])) {
            $query->whereHas('categories', fn (Builder $categoryQuery) => $categoryQuery->whereKey($filters['category_id']));
        }

        return $query
            ->when($filters['is_active'] ?? null, fn ($q, $v) => $q->where('is_active', $v))
            ->when($filters['brand_id'] ?? null, fn ($q, $v) => $q->where('brand_id', $v))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($filters['vendor_id'] ?? null, fn ($q, $v) => $q->where('vendor_id', $v))
            ->orderBy(
                $filters['sort_by'] ?? 'sort_order',
                $filters['direction'] ?? 'asc'
            );
    }
}
