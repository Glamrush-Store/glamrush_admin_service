<?php

use App\Domain\Product\UseCases\CreateProductUseCase;
use App\Domain\ProductImport\Services\ProductImportProcessor;
use App\Domain\ProductImport\Services\RemoteProductImageDownloader;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImport;
use App\Models\SkuAttributeCode;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

function storedProductImport(string $csv, string $imageFailurePolicy = 'warn'): ProductImport
{
    Storage::fake('local');
    $vendor = Vendor::factory()->create();
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    $path = 'product-imports/test/source.csv';
    Storage::disk('local')->put($path, $csv);

    return ProductImport::query()->create([
        'vendor_id' => $vendor->id,
        'brand_id' => $brand->id,
        'category_ids' => [$category->id],
        'primary_category_id' => $category->id,
        'default_product_status' => 'draft',
        'image_failure_policy' => $imageFailurePolicy,
        'original_filename' => 'products.csv',
        'file_path' => $path,
        'file_hash' => hash('sha256', $csv),
    ]);
}

it('processes valid products and records progress', function () {
    $import = storedProductImport(
        "product_key,name,type,price,manage_stock,stock_quantity,in_stock\n".
        "P001,Classic Oud,simple,25000,true,12,true\n"
    );

    $createProduct = Mockery::mock(CreateProductUseCase::class);
    $createProduct->shouldReceive('execute')
        ->once()
        ->with(Mockery::on(fn (array $data) => $data['vendor_id'] === $import->vendor_id
            && $data['brand_id'] === $import->brand_id
            && $data['category_ids'] === $import->category_ids
            && $data['primary_category_id'] === $import->primary_category_id
            && $data['price'] === 25000.0), true)
        ->andReturn(new Product);
    app()->instance(CreateProductUseCase::class, $createProduct);

    app(ProductImportProcessor::class)->process($import);

    $import->refresh();
    expect($import->status)->toBe(ProductImport::STATUS_COMPLETED)
        ->and($import->total_rows)->toBe(1)
        ->and($import->total_products)->toBe(1)
        ->and($import->processed_products)->toBe(1)
        ->and($import->imported_products)->toBe(1)
        ->and($import->failed_products)->toBe(0);
});

it('continues after an invalid product and reports a partial import', function () {
    $import = storedProductImport(
        "product_key,name,type,price,manage_stock,stock_quantity,in_stock\n".
        "BAD,Broken Product,simple,invalid,true,2,true\n".
        "GOOD,Valid Product,simple,5000,true,4,true\n"
    );

    $createProduct = Mockery::mock(CreateProductUseCase::class);
    $createProduct->shouldReceive('execute')->once()->with(Mockery::type('array'), true)->andReturn(new Product);
    app()->instance(CreateProductUseCase::class, $createProduct);

    app(ProductImportProcessor::class)->process($import);

    $import->refresh();
    expect($import->status)->toBe(ProductImport::STATUS_COMPLETED_WITH_ERRORS)
        ->and($import->processed_products)->toBe(2)
        ->and($import->imported_products)->toBe(1)
        ->and($import->failed_products)->toBe(1)
        ->and($import->issues[0]['product_key'])->toBe('BAD');
});

it('imports a product with a warning when an image cannot be downloaded', function () {
    $import = storedProductImport(
        "product_key,name,type,price,manage_stock,stock_quantity,in_stock,product_image_urls\n".
        "P001,Classic Oud,simple,25000,true,12,true,https://example.com/missing.jpg\n"
    );

    $downloader = Mockery::mock(RemoteProductImageDownloader::class);
    $downloader->shouldReceive('download')->once()->andThrow(new RuntimeException('Image unavailable.'));
    app()->instance(RemoteProductImageDownloader::class, $downloader);

    $createProduct = Mockery::mock(CreateProductUseCase::class);
    $createProduct->shouldReceive('execute')
        ->once()
        ->with(Mockery::on(fn (array $data) => $data['photos'] === []), true)
        ->andReturn(new Product);
    app()->instance(CreateProductUseCase::class, $createProduct);

    app(ProductImportProcessor::class)->process($import);

    $import->refresh();
    expect($import->status)->toBe(ProductImport::STATUS_COMPLETED_WITH_WARNINGS)
        ->and($import->imported_products)->toBe(1)
        ->and($import->warning_count)->toBe(1)
        ->and($import->issues[0]['severity'])->toBe('warning');
});

it('creates a variable product and its variants through the real product use case', function () {
    SkuAttributeCode::query()->insert([
        ['type' => 'size', 'value' => 'Small', 'code' => 'S', 'is_active' => true],
        ['type' => 'size', 'value' => 'Large', 'code' => 'L', 'is_active' => true],
    ]);

    $import = storedProductImport(
        "product_key,name,type,variant_attributes,variant_price,variant_manage_stock,variant_stock_quantity,variant_in_stock,variant_is_default,variant_status,variant_sort_order\n".
        "P002,Signature Perfume,variable,size=Small,18000,true,10,true,true,active,1\n".
        "P002,Signature Perfume,variable,size=Large,26000,true,5,true,false,active,2\n"
    );

    app(ProductImportProcessor::class)->process($import);

    $product = Product::query()->where('name', 'Signature Perfume')->firstOrFail();
    expect($import->refresh()->status)->toBe(ProductImport::STATUS_COMPLETED)
        ->and($product->type)->toBe('variable')
        ->and($product->vendor_id)->toBe($import->vendor_id)
        ->and($product->variants)->toHaveCount(2)
        ->and($product->variants->pluck('sku')->all())->toBe([
            $product->sku.'-S',
            $product->sku.'-L',
        ])
        ->and($product->defaultVariant->attributes)->toBe([
            ['type' => 'size', 'value' => 'Small'],
        ]);
});

it('creates imported variable product variants without attributes', function () {
    $import = storedProductImport(
        "product_key,name,type,variant_attributes,variant_price,variant_manage_stock,variant_stock_quantity,variant_in_stock,variant_is_default,variant_status,variant_sort_order\n".
        "P003,Unconfigured Perfume,variable,,18000,true,10,true,true,active,1\n".
        "P003,Unconfigured Perfume,variable,,26000,true,5,true,false,active,2\n"
    );

    app(ProductImportProcessor::class)->process($import);

    $product = Product::query()->where('name', 'Unconfigured Perfume')->firstOrFail();

    expect($import->refresh()->status)->toBe(ProductImport::STATUS_COMPLETED)
        ->and($product->variants)->toHaveCount(2)
        ->and($product->variants->pluck('sku')->all())->toBe([
            $product->sku.'-VAR-001',
            $product->sku.'-VAR-002',
        ])
        ->and($product->variants->pluck('attributes')->all())->toBe([[], []]);
});

afterEach(fn () => Mockery::close());
