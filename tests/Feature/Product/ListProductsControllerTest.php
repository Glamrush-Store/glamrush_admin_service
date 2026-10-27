<?php

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

it('filters products by status', function () {
    Permission::findOrCreate('View_Product', 'sanctum');

    $user = User::factory()->create();
    $user->givePermissionTo('View_Product');
    Sanctum::actingAs($user);

    Brand::factory()->create();
    Vendor::factory()->create();

    $draft = Product::factory()->create(['status' => 'draft']);
    Product::factory()->create(['status' => 'published']);
    Product::factory()->create(['status' => 'archived']);

    $response = $this->getJson('/api/v1/products?status=draft&per_page=15');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $draft->id)
        ->assertJsonPath('data.0.status', 'draft');
});

it('sorts products by creation date using sort_dir', function () {
    Permission::findOrCreate('View_Product', 'sanctum');

    $user = User::factory()->create();
    $user->givePermissionTo('View_Product');
    Sanctum::actingAs($user);

    $brand = Brand::factory()->create();
    $vendor = Vendor::factory()->create();

    $createProduct = function (string $name, int $sequence, $createdAt) use ($brand, $vendor): Product {
        $product = Product::query()->create([
            'name' => $name,
            'slug' => str($name)->slug(),
            'sequence' => $sequence,
            'type' => 'simple',
            'status' => 'draft',
            'brand_id' => $brand->id,
            'vendor_id' => $vendor->id,
            'price' => 1000,
            'manage_stock' => false,
            'stock_quantity' => 0,
            'in_stock' => true,
        ]);

        $product->forceFill(['created_at' => $createdAt])->saveQuietly();

        return $product->refresh();
    };

    $newest = $createProduct('Newest Product', 101, now()->subDay());
    $oldest = $createProduct('Oldest Product', 102, now()->subDays(3));
    $middle = $createProduct('Middle Product', 103, now()->subDays(2));

    $this->getJson('/api/v1/products?page=1&per_page=15&sort_by=created_at&sort_dir=asc')
        ->assertOk()
        ->assertJsonPath('data.0.id', $oldest->id)
        ->assertJsonPath('data.1.id', $middle->id)
        ->assertJsonPath('data.2.id', $newest->id);

    $this->getJson('/api/v1/products?page=1&per_page=15&sort_by=created_at&sort_dir=desc')
        ->assertOk()
        ->assertJsonPath('data.0.id', $newest->id)
        ->assertJsonPath('data.1.id', $middle->id)
        ->assertJsonPath('data.2.id', $oldest->id);
});
