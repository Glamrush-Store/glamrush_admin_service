<?php

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

it('updates nested sale pricing and synchronizes the simple product variant', function () {
    Permission::findOrCreate('Update_Product', 'sanctum');

    $user = User::factory()->create();
    $user->givePermissionTo('Update_Product');
    Sanctum::actingAs($user);

    $brand = Brand::factory()->create();
    $vendor = Vendor::factory()->create();
    $product = Product::query()->create([
        'name' => 'Sale Product',
        'slug' => 'sale-product',
        'sku' => 'SALE-001',
        'sequence' => 900001,
        'type' => 'simple',
        'status' => 'draft',
        'brand_id' => $brand->id,
        'vendor_id' => $vendor->id,
        'price' => 10000,
        'sale_price' => 9500,
        'sale_starts_at' => now()->subDays(2),
        'sale_ends_at' => now()->subDay(),
        'manage_stock' => false,
        'stock_quantity' => 0,
        'in_stock' => true,
    ]);

    $saleStartsAt = now()->addDay()->startOfSecond();
    $saleEndsAt = now()->addDays(7)->startOfSecond();

    $this->putJson("/api/v1/products/{$product->id}", [
        'pricing' => [
            'sale_price' => 8000,
            'sale_starts_at' => $saleStartsAt->toISOString(),
            'sale_ends_at' => $saleEndsAt->toISOString(),
        ],
    ])->assertOk();

    $product->refresh();
    $variant = $product->defaultVariant()->firstOrFail();

    expect((float) $product->sale_price)->toBe(8000.0)
        ->and($product->sale_starts_at->equalTo($saleStartsAt))->toBeTrue()
        ->and($product->sale_ends_at->equalTo($saleEndsAt))->toBeTrue()
        ->and((float) $variant->sale_price)->toBe(8000.0)
        ->and($variant->sale_starts_at->equalTo($saleStartsAt))->toBeTrue()
        ->and($variant->sale_ends_at->equalTo($saleEndsAt))->toBeTrue();
});
