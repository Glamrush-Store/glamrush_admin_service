<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('type', 'simple')
            ->orderBy('id')
            ->chunkById(250, function ($products): void {
                foreach ($products as $product) {
                    $variant = DB::table('product_variants')
                        ->where('product_id', $product->id)
                        ->orderByDesc('is_default')
                        ->orderBy('sort_order')
                        ->first();

                    $values = [
                        'sku' => $product->sku ?: 'SIMPLE-'.$product->id,
                        'is_default' => true,
                        'price' => $product->price,
                        'sale_price' => $product->sale_price,
                        'sale_starts_at' => $product->sale_starts_at,
                        'sale_ends_at' => $product->sale_ends_at,
                        'manage_stock' => $product->manage_stock,
                        'stock_quantity' => $product->stock_quantity,
                        'in_stock' => $product->in_stock,
                        'attributes' => json_encode([]),
                        'sort_order' => 0,
                        'status' => $product->status === 'published' ? 'active' : 'disabled',
                        'updated_at' => now(),
                    ];

                    if ($variant) {
                        DB::table('product_variants')
                            ->where('product_id', $product->id)
                            ->where('id', '!=', $variant->id)
                            ->delete();
                        DB::table('product_variants')->where('id', $variant->id)->update($values);

                        continue;
                    }

                    DB::table('product_variants')->insert($values + [
                        'id' => (string) Str::ulid(),
                        'product_id' => $product->id,
                        'created_at' => now(),
                    ]);
                }
            }, 'id');
    }

    public function down(): void
    {
        // This data repair is intentionally irreversible.
    }
};
