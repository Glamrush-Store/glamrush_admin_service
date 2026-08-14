<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Domain\Product\UseCases\CreateProductUseCase;
use App\Domain\Product\UseCases\UpdateProductUseCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SkuAttributeCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('creates a simple product with one sellable default variant', function () {

    $brand = Brand::factory()->create([
        'id' => '01KGWBRMZTEKQMKAQ4YYJ79GR8',
    ]);

    $category = Category::factory()->create([
        'id' => '01KGWBRMZTEKQMKAQ4zzJ79GR8',
    ]);

    $productData = Product::factory()->make([
        'brand_id' => $brand->id,
        'category_ids' => [$category->id],
        'primary_category_id' => $category->id,
        'type' => 'simple',
        'status' => 'published',
    ])->toArray();

    $createProduct = app(CreateProductUseCase::class);

    $result = $createProduct->execute($productData);

    expect($result)
        ->toBeInstanceOf(Product::class)
        ->and($result->type)->toBe('simple')
        ->and($result->variants)->toHaveCount(1)
        ->and($result->variants->first()->is_default)->toBeTrue()
        ->and($result->variants->first()->sku)->toBe($result->sku)
        ->and((float) $result->variants->first()->price)->toBe((float) $result->price)
        ->and($result->variants->first()->status)->toBe('active');

});

it('creates variable product with multiple variants', function () {

    $brand = Brand::factory()->create([
        'id' => '01KGWBRMZTEKQMKAQ4YYJ79GR8',
    ]);

    $category = Category::factory()->create([
        'id' => '01KGWBRMZTEKQMKAQ4zzJ79GR8',
    ]);

    SkuAttributeCode::factory()->count(12)->create()->toArray();

    $productVariantData = ProductVariant::factory()
        ->count(2)
        ->sequence(fn ($sequence) => [
            'attributes' => [
                ['type' => 'color', 'value' => ['Red', 'Blue'][$sequence->index]],
                ['type' => 'size', 'value' => ['Small', 'Medium'][$sequence->index]],
            ],
        ])
        ->make([
            'sale_starts_at' => now(),
            'sale_ends_at' => now()->addDays(5),
        ])
        ->toArray();

    $productData = Product::factory()->make([
        'brand_id' => $brand->id,
        'category_ids' => [$category->id],
        'primary_category_id' => $category->id,
        'type' => 'variable',
        'status' => 'published',
        'variants' => $productVariantData,
    ])->toArray();

    $createProduct = app(CreateProductUseCase::class);

    $result = $createProduct->execute($productData);

    expect($result)
        ->toBeInstanceOf(Product::class)
        ->and($result->type)->toBe('variable')
        ->and($result->variants)->toHaveCount(2);
});

it('keeps the simple product default variant synchronized when the product changes', function () {
    $product = Product::factory()->simple()->create([
        'type' => 'simple',
        'status' => 'published',
        'sku' => 'SIMPLE-SYNC',
        'price' => 1500,
        'manage_stock' => true,
        'stock_quantity' => 8,
        'in_stock' => true,
    ]);

    $result = app(UpdateProductUseCase::class)->execute($product, [
        'price' => 1750,
        'stock_quantity' => 3,
        'in_stock' => true,
    ]);

    expect($result->variants)->toHaveCount(1)
        ->and($result->variants->first()->is_default)->toBeTrue()
        ->and((float) $result->variants->first()->price)->toBe(1750.0)
        ->and($result->variants->first()->stock_quantity)->toBe(3);
});

afterEach(fn () => Mockery::close());
