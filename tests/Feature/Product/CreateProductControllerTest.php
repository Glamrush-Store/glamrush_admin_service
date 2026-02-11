<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Domain\Product\UseCases\CreateProductUseCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

it('creates a product successfully', function () {

    $permission = Permission::create([
        'name' => 'Create_Product',
        'guard_name' => 'sanctum',
    ]);

    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'old-password',
    ]);

    $user->givePermissionTo('Create_Product');

    Sanctum::actingAs($user);

    $payload = [
        'name' => 'Basic T-Shirt',
        'type' => 'simple',
        'status' => 'published',
        'category_id' => Category::factory()->create()->id,
        'brand_id' => Brand::factory()->create()->id,
        'price' => 5000,
        'sale_price' => 4500,
        'sale_starts_at' => now()->addDays(1),
        'sale_ends_at' => now()->addDays(3),
        'manage_stock' => true,
        'stock_quantity' => 10,
        'in_stock' => true,
    ];

    $product = Product::factory()->make([
        'name' => 'Basic T-Shirt',
    ]);

    $useCase = Mockery::mock(CreateProductUseCase::class);
    $useCase
        ->shouldReceive('execute')
        ->once()
        ->withAnyArgs()
        ->andReturn($product);

    app()->instance(CreateProductUseCase::class, $useCase);

    $response = $this->postJson('/api/v1/products', $payload);

    $response
        ->assertStatus(201)
        ->assertJsonPath('data.name', 'Basic T-Shirt');
});
