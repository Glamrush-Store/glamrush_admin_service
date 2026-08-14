<?php

namespace App\Domain\Product\Actions;

use App\Models\Product;
use App\Models\ProductVariant;

final class SyncSimpleProductVariantAction
{
    public function run(Product $product): ?ProductVariant
    {
        if (! $product->isSimple()) {
            return null;
        }

        $variant = $product->variants()
            ->where('is_default', true)
            ->first() ?? $product->variants()->first();

        $variant ??= new ProductVariant(['product_id' => $product->id]);

        $product->variants()
            ->when($variant->exists, fn ($query) => $query->whereKeyNot($variant->id))
            ->delete();

        $variant->fill([
            'sku' => $product->sku ?: 'SIMPLE-'.$product->id,
            'is_default' => true,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'sale_starts_at' => $product->sale_starts_at,
            'sale_ends_at' => $product->sale_ends_at,
            'manage_stock' => $product->manage_stock,
            'stock_quantity' => $product->stock_quantity,
            'in_stock' => $product->in_stock,
            'attributes' => [],
            'sort_order' => 0,
            'status' => $product->status === 'published' ? 'active' : 'disabled',
        ]);
        $variant->save();

        return $variant;
    }
}
