<?php

use App\Jobs\ProcessProductImportJob;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductImport;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function productImportUser(array $permissions): User
{
    $user = User::factory()->create();

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'sanctum');
    }

    $user->givePermissionTo($permissions);
    Sanctum::actingAs($user);

    return $user;
}

it('queues a configured product import', function () {
    Storage::fake('local');
    Queue::fake();
    productImportUser(['Create_Product']);

    $vendor = Vendor::factory()->create();
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    $csv = "product_key,name,type,price,manage_stock,stock_quantity,in_stock\nP001,Classic Oud,simple,25000,true,12,true\n";

    $response = $this->post('/api/v1/product-imports', [
        'file' => UploadedFile::fake()->createWithContent('products.csv', $csv),
        'vendor_id' => $vendor->id,
        'brand_id' => $brand->id,
        'category_ids' => [$category->id],
        'primary_category_id' => $category->id,
        'default_status' => 'draft',
        'image_failure_policy' => 'warn',
    ], ['Accept' => 'application/json']);

    $response->assertAccepted()
        ->assertJsonPath('data.status', ProductImport::STATUS_PENDING)
        ->assertJsonPath('data.configuration.vendor_id', $vendor->id)
        ->assertJsonPath('data.configuration.brand_id', $brand->id)
        ->assertJsonPath('data.configuration.category_ids.0', $category->id)
        ->assertJsonPath('data.configuration.primary_category_id', $category->id);

    $import = ProductImport::query()->firstOrFail();
    Storage::disk('local')->assertExists($import->file_path);
    Queue::assertPushed(ProcessProductImportJob::class, fn ($job) => $job->productImportId === $import->id);
});

it('validates all upload configuration fields', function () {
    productImportUser(['Create_Product']);

    $this->post('/api/v1/product-imports', [], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['file', 'vendor_id', 'brand_id', 'category_ids', 'primary_category_id', 'default_status']);
});

it('lists and shows product imports', function () {
    $user = productImportUser(['View_Product']);
    $vendor = Vendor::factory()->create();
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();

    $import = ProductImport::query()->create([
        'user_id' => $user->id,
        'vendor_id' => $vendor->id,
        'brand_id' => $brand->id,
        'category_ids' => [$category->id],
        'primary_category_id' => $category->id,
        'default_product_status' => 'draft',
        'image_failure_policy' => 'warn',
        'original_filename' => 'products.csv',
        'file_path' => 'product-imports/source.csv',
        'file_hash' => str_repeat('a', 64),
    ]);

    $this->getJson('/api/v1/product-imports')
        ->assertOk()
        ->assertJsonPath('data.0.id', $import->id);

    $this->getJson('/api/v1/product-imports/'.$import->id)
        ->assertOk()
        ->assertJsonPath('data.id', $import->id);
});

it('downloads the product import template', function () {
    productImportUser(['View_Product']);

    $this->get('/api/v1/product-imports/template')
        ->assertOk()
        ->assertDownload('product-import-sample.csv');
});
