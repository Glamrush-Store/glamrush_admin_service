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
    private const SORTABLE_COLUMNS = [
        'name',
        'sequence',
        'type',
        'status',
        'price',
        'sale_price',
        'stock_quantity',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    public function run(array $filters): Builder
    {
        $requestedSort = (string) ($filters['sort_by'] ?? 'sort_order');
        $sortBy = in_array($requestedSort, self::SORTABLE_COLUMNS, true)
            ? $requestedSort
            : 'sort_order';

        $requestedDirection = strtolower((string) ($filters['sort_dir'] ?? $filters['direction'] ?? 'asc'));
        $sortDirection = in_array($requestedDirection, ['asc', 'desc'], true)
            ? $requestedDirection
            : 'asc';

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
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($filters['vendor_id'] ?? null, fn ($q, $v) => $q->where('vendor_id', $v))
            ->orderBy("products.{$sortBy}", $sortDirection)
            ->orderBy('products.id', $sortDirection);
    }
}
