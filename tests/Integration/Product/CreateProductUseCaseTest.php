<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Domain\Product\UseCases\CreateProductUseCase;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SkuAttributeCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('creates a simple product without variants', function () {

    $brand = Brand::factory()->create([
        'id' => '01KGWBRMZTEKQMKAQ4YYJ79GR8',
    ]);

    $category = Brand::factory()->create([
        'id' => '01KGWBRMZTEKQMKAQ4zzJ79GR8',
    ]);

    $productData = Product::factory()->make([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'type' => 'simple',
        'status' => 'published',
    ])->toArray();

    $createProduct = app(CreateProductUseCase::class);

    $result = $createProduct->execute($productData);

    expect($result)
        ->toBeInstanceOf(Product::class)
        ->and($result->type)->toBe('simple')
        ->and($result->variants)->toHaveCount(0);

});

it('creates variable product with multiple variants', function () {

    $brand = Brand::factory()->create([
        'id' => '01KGWBRMZTEKQMKAQ4YYJ79GR8',
    ]);

    $category = Brand::factory()->create([
        'id' => '01KGWBRMZTEKQMKAQ4zzJ79GR8',
    ]);

    SkuAttributeCode::factory()->count(12)->create()->toArray();

    $productVariantData = ProductVariant::factory()
        ->count(2)
        ->sequence(fn ($sequence) => [
            'attributes' => [
                'color' => ['Red', 'Blue'][$sequence->index],
                'size' => ['Small', 'Medium'][$sequence->index],
            ],
        ])
        ->make([
            'sale_starts_at' => now(),
            'sale_ends_at' => now()->addDays(5),
        ])
        ->toArray();

    $productData = Product::factory()->make([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
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

afterEach(fn () => Mockery::close());
