<?php

use App\Domain\ProductImport\Exceptions\InvalidProductImportException;
use App\Domain\ProductImport\Services\ProductImportCsvParser;
use App\Domain\ProductImport\Services\ProductImportRowMapper;
use App\Models\ProductImport;
use App\Models\SkuAttributeCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

function productImportConfiguration(): ProductImport
{
    return new ProductImport([
        'vendor_id' => '01K00000000000000000000001',
        'brand_id' => '01K00000000000000000000002',
        'category_ids' => ['01K00000000000000000000003', '01K00000000000000000000004'],
        'primary_category_id' => '01K00000000000000000000003',
        'category_sequences' => [
            '01K00000000000000000000003' => 1,
            '01K00000000000000000000004' => 2,
        ],
        'default_product_status' => 'draft',
        'image_failure_policy' => 'warn',
    ]);
}

it('maps a simple product using upload configuration relationships', function () {
    $mapped = app(ProductImportRowMapper::class)->map([[
        'product_key' => 'P001',
        'name' => 'Classic Oud',
        'type' => 'simple',
        'status' => '',
        'price' => '25000',
        'sale_price' => '22500',
        'sale_starts_at' => '',
        'sale_ends_at' => '',
        'manage_stock' => 'true',
        'stock_quantity' => '12',
        'in_stock' => 'true',
        'product_image_urls' => 'https://example.com/front.jpg|https://example.com/side.jpg',
        '_row_number' => 2,
    ]], productImportConfiguration());

    expect($mapped['data']['vendor_id'])->toBe('01K00000000000000000000001')
        ->and($mapped['data']['brand_id'])->toBe('01K00000000000000000000002')
        ->and($mapped['data']['category_ids'])->toBe([
            '01K00000000000000000000003',
            '01K00000000000000000000004',
        ])
        ->and($mapped['data']['primary_category_id'])->toBe('01K00000000000000000000003')
        ->and($mapped['data']['status'])->toBe('draft')
        ->and($mapped['data']['price'])->toBe(25000.0)
        ->and($mapped['product_image_urls'])->toHaveCount(2);
});

it('maps variable rows and requires exactly one default variant', function () {
    SkuAttributeCode::query()->create([
        'type' => 'size',
        'value' => 'Small',
        'code' => 'S',
        'is_active' => true,
    ]);
    SkuAttributeCode::query()->create([
        'type' => 'size',
        'value' => 'Large',
        'code' => 'L',
        'is_active' => true,
    ]);

    $base = [
        'product_key' => 'P002',
        'name' => 'Signature Perfume',
        'type' => 'variable',
        'status' => '',
        'variant_manage_stock' => 'true',
        'variant_in_stock' => 'true',
        'variant_status' => 'active',
    ];

    $mapped = app(ProductImportRowMapper::class)->map([
        [...$base, '_row_number' => 2, 'variant_attributes' => 'size=Small', 'variant_price' => '18000', 'variant_stock_quantity' => '10', 'variant_is_default' => 'true', 'variant_sort_order' => '1'],
        [...$base, '_row_number' => 3, 'variant_attributes' => 'size=Large', 'variant_price' => '26000', 'variant_stock_quantity' => '5', 'variant_is_default' => 'false', 'variant_sort_order' => '2'],
    ], productImportConfiguration());

    expect($mapped['data']['variants'])->toHaveCount(2)
        ->and($mapped['data']['variants'][0]['attributes'])->toBe([['type' => 'size', 'value' => 'Small']])
        ->and($mapped['data']['variants'][0]['is_default'])->toBeTrue();
});

it('maps multiple variable rows without attributes', function () {
    $base = [
        'product_key' => 'P002',
        'name' => 'Signature Perfume',
        'type' => 'variable',
        'status' => '',
        'variant_attributes' => '',
        'variant_manage_stock' => 'true',
        'variant_in_stock' => 'true',
        'variant_status' => 'active',
    ];

    $mapped = app(ProductImportRowMapper::class)->map([
        [...$base, '_row_number' => 2, 'variant_price' => '18000', 'variant_stock_quantity' => '10', 'variant_is_default' => 'true', 'variant_sort_order' => '1'],
        [...$base, '_row_number' => 3, 'variant_price' => '26000', 'variant_stock_quantity' => '5', 'variant_is_default' => 'false', 'variant_sort_order' => '2'],
    ], productImportConfiguration());

    expect($mapped['data']['variants'])->toHaveCount(2)
        ->and($mapped['data']['variants'][0]['attributes'])->toBe([])
        ->and($mapped['data']['variants'][1]['attributes'])->toBe([]);
});

it('rejects variable products without exactly one default variant', function () {
    SkuAttributeCode::query()->create([
        'type' => 'size',
        'value' => 'Small',
        'code' => 'S',
        'is_active' => true,
    ]);

    $row = [
        'product_key' => 'P002',
        'name' => 'Signature Perfume',
        'type' => 'variable',
        'variant_attributes' => 'size=Small',
        'variant_price' => '18000',
        'variant_manage_stock' => 'true',
        'variant_stock_quantity' => '10',
        'variant_in_stock' => 'true',
        'variant_is_default' => 'false',
        'variant_status' => 'active',
        '_row_number' => 2,
    ];

    expect(fn () => app(ProductImportRowMapper::class)->map([$row], productImportConfiguration()))
        ->toThrow(InvalidProductImportException::class, 'exactly one default');
});

it('maps all bundled scraped products as simple products without images or attributes', function () {
    $parsed = app(ProductImportCsvParser::class)->parse(
        resource_path('files/product-import-sample.csv')
    );
    $mapper = app(ProductImportRowMapper::class);
    $mappedProducts = collect($parsed['groups'])->map(
        fn (array $rows) => $mapper->map($rows, productImportConfiguration())
    );

    expect($parsed['row_count'])->toBe(71)
        ->and($mappedProducts)->toHaveCount(71)
        ->and($mappedProducts->every(fn (array $mapped) => $mapped['data']['type'] === 'simple'))->toBeTrue()
        ->and($mappedProducts->every(fn (array $mapped) => $mapped['product_image_urls'] === []))->toBeTrue()
        ->and($mappedProducts['TOSD001']['data']['price'])->toBe(5500.0)
        ->and($mappedProducts['TOSD001']['data']['sale_price'])->toBe(4950.0);
});
