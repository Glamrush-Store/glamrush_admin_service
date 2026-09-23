<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SkuAttributeCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::findOrCreate('Update_Product', 'sanctum');

    $user = User::factory()->create();
    $user->givePermissionTo('Update_Product');
    Sanctum::actingAs($user);

    $this->product = Product::query()->create([
        'name' => 'Signature Scent',
        'sku' => 'SIG-SCENT',
        'sequence' => 1,
        'slug' => 'signature-scent',
        'type' => 'variable',
        'status' => 'published',
    ]);

    $this->existingVariant = ProductVariant::query()->create([
        'product_id' => $this->product->id,
        'sku' => 'SIG-SCENT-30ML-SPR',
        'is_default' => true,
        'price' => 15000,
        'manage_stock' => true,
        'stock_quantity' => 5,
        'in_stock' => true,
        'attributes' => [
            ['type' => 'volume', 'value' => '30ml'],
            ['type' => 'format', 'value' => 'Spray'],
        ],
        'sort_order' => 0,
        'status' => 'active',
    ]);

    SkuAttributeCode::query()->insert([
        [
            'type' => 'volume',
            'value' => '50ml',
            'code' => '50ML',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'type' => 'format',
            'value' => 'Oil',
            'code' => 'OIL',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
});

it('adds a variant to an existing variable product without replacing existing variants', function () {
    $response = $this->postJson("/api/v1/products/{$this->product->id}/variants", [
        'price' => 21000,
        'manage_stock' => true,
        'stock_quantity' => 8,
        'in_stock' => true,
        'attributes' => [
            ['type' => 'volume', 'value' => '50ml'],
            ['type' => 'format', 'value' => 'Oil'],
        ],
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Product variant created')
        ->assertJsonPath('data.product_id', $this->product->id)
        ->assertJsonPath('data.sku', 'SIG-SCENT-OIL-50ML')
        ->assertJsonPath('data.is_default', false)
        ->assertJsonPath('data.status', 'active');

    expect($this->product->variants()->count())->toBe(2)
        ->and(ProductVariant::query()->find($this->existingVariant->id))->not->toBeNull();
});

it('makes a newly added default variant the only default', function () {
    $response = $this->postJson("/api/v1/products/{$this->product->id}/variants", [
        'price' => 21000,
        'is_default' => true,
        'attributes' => [
            ['type' => 'volume', 'value' => '50ml'],
            ['type' => 'format', 'value' => 'Oil'],
        ],
    ]);

    $response->assertCreated()->assertJsonPath('data.is_default', true);

    expect($this->existingVariant->fresh()->is_default)->toBeFalse()
        ->and($this->product->variants()->where('is_default', true)->count())->toBe(1);
});

it('rejects a duplicate attribute combination regardless of attribute order', function () {
    $response = $this->postJson("/api/v1/products/{$this->product->id}/variants", [
        'price' => 15000,
        'attributes' => [
            ['type' => 'format', 'value' => 'Spray'],
            ['type' => 'volume', 'value' => '30ml'],
        ],
    ]);

    $response
        ->assertConflict()
        ->assertJsonPath('message', 'A variant with this attribute combination already exists.');

    expect($this->product->variants()->count())->toBe(1);
});

it('rejects adding variants to a simple product', function () {
    $this->product->update(['type' => 'simple']);

    $response = $this->postJson("/api/v1/products/{$this->product->id}/variants", [
        'price' => 21000,
        'attributes' => [
            ['type' => 'volume', 'value' => '50ml'],
        ],
    ]);

    $response
        ->assertConflict()
        ->assertJsonPath('message', 'Variants can only be added to variable products.');
});

it('validates the variant payload', function () {
    $response = $this->postJson("/api/v1/products/{$this->product->id}/variants", [
        'price' => -1,
        'attributes' => [],
        'status' => 'inactive',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['price', 'attributes', 'status']);
});

it('requires the update product permission', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson("/api/v1/products/{$this->product->id}/variants", [
        'price' => 21000,
        'attributes' => [
            ['type' => 'volume', 'value' => '50ml'],
        ],
    ])->assertForbidden();
});
