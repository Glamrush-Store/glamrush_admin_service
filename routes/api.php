<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Http\Controllers\Auth\ConfirmPasswordResetController;
use App\Http\Controllers\Auth\CreateAccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RequestPasswordResetController;
use App\Http\Controllers\Auth\SelfController;
use App\Http\Controllers\Auth\VerifyPasswordResetCodeController;
use App\Http\Controllers\Brand\CreateBrandController;
use App\Http\Controllers\Brand\DeleteBrandController;
use App\Http\Controllers\Brand\ListBrandsController;
use App\Http\Controllers\Brand\ShowBrandController;
use App\Http\Controllers\Brand\UpdateBrandController;
use App\Http\Controllers\Category\CreateCategoryController;
use App\Http\Controllers\Category\DeleteCategoryController;
use App\Http\Controllers\Category\ListCategoriesController;
use App\Http\Controllers\Category\ShowCategoryController;
use App\Http\Controllers\Category\UpdateCategoryController;
use App\Http\Controllers\Media\DeleteMediaController;
use App\Http\Controllers\Product\CreateProductController;
use App\Http\Controllers\Product\DeleteProductController;
use App\Http\Controllers\Product\ListProductsController;
use App\Http\Controllers\Product\ShowProductController;
use App\Http\Controllers\Product\UpdateProductController;
use App\Http\Controllers\ProductVariant\DeleteProductVariantController;
use App\Http\Controllers\ProductVariant\ShowProductVariantController;
use App\Http\Controllers\ProductVariant\UpdateProductVariantController;
use App\Http\Controllers\SkuAttributeCode\SkuAttributeCodeController;
use App\Http\Controllers\Vendor\CreateVendorController;
use App\Http\Controllers\Vendor\DeleteVendorController;
use App\Http\Controllers\Vendor\ListVendorController;
use App\Http\Controllers\Vendor\ShowVendorController;
use App\Http\Controllers\Vendor\UpdateVendorController;

use Illuminate\Support\Facades\Route;

// ========================================================
// AUTH API ROUTES
// ========================================================

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok']));
    Route::post('/account/create', CreateAccountController::class)->middleware('auth:sanctum', 'permission:Create_User');
    Route::post('/account/login', LoginController::class);
    Route::post('/account/logout', LogoutController::class)->middleware('auth:sanctum');
    Route::post('/password/reset/request', RequestPasswordResetController::class);
    Route::post('/password/reset/verify', VerifyPasswordResetCodeController::class);
    Route::post('/password/reset/confirm', ConfirmPasswordResetController::class)->middleware(['auth:sanctum', 'ability:password:reset']);
    Route::get('/whoami', SelfController::class)->middleware(['auth:sanctum']);
});

// ========================================================
//  CATEGORY API ROUTES
// ========================================================

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/categories', ListCategoriesController::class)->middleware('permission:View_Category');
    Route::get('/categories/{category}', ShowCategoryController::class)->middleware('permission:View_Category');
    Route::post('/categories', CreateCategoryController::class)->middleware('permission:Create_Category');
    Route::put('/categories/{category}', UpdateCategoryController::class)->middleware('permission:Update_Category');
    Route::delete('/categories/{category}', DeleteCategoryController::class)->middleware('permission:Delete_Category');
});

// ========================================================
//  BRAND API ROUTES
// ========================================================

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/brands', ListBrandsController::class)->middleware('permission:View_Brand');
    Route::get('/brands/{brand}', ShowBrandController::class)->middleware('permission:View_Brand');
    Route::post('/brands', CreateBrandController::class)->middleware('permission:Create_Brand');
    Route::put('/brands/{brand}', UpdateBrandController::class)->middleware('permission:Update_Brand');
    Route::delete('/brands/{brand}', DeleteBrandController::class)->middleware('permission:Delete_Brand');
});

// ========================================================
// VENDORS API ROUTES
// ========================================================
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/vendors', ListVendorController::class)->middleware('permission:View_Vendor');
    Route::get('/vendors/{vendor}', ShowVendorController::class)->middleware('permission:View_Vendor');
    Route::post('/vendors', CreateVendorController::class)->middleware('permission:Create_Vendor');
    Route::put('/vendors/{vendor}', UpdateVendorController::class)->middleware('permission:Update_Vendor');
    Route::delete('/vendors/{vendor}', DeleteVendorController::class)->middleware('permission:Delete_Vendor');
});

// ========================================================
//  PRODUCTS API ROUTES
// ========================================================

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/products', ListProductsController::class)->middleware('permission:View_Product');
    Route::get('/products/{product}', ShowProductController::class)->middleware('permission:View_Product');
    Route::post('/products', CreateProductController::class)->middleware('permission:Create_Product');
    Route::put('/products/{product}', UpdateProductController::class)->middleware('permission:Update_Product');
    Route::delete('/products/{product}', DeleteProductController::class)->middleware('permission:Delete_Product');
});



// ========================================================
//  PRODUCT VARIANT ROUTES
// ========================================================
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/productvariants/{variant}', ShowProductVariantController::class)->middleware('permission:View_Product');
    Route::put('/productvariants/{variant}', UpdateProductVariantController::class)->middleware('permission:Update_Product');
    Route::delete('/productvariants/{variant}', DeleteProductVariantController::class)->middleware('permission:Delete_Product');
});




// ========================================================
// SKU ATTRIBUTE CODES API ROUTES
// ========================================================
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/sku-attribute-code', [SkuAttributeCodeController::class, 'index'])->middleware('permission:View_Vendor');
    Route::get('/sku-attribute-code/list/types', [SkuAttributeCodeController::class, 'types'])->middleware('permission:View_Vendor');
    Route::get('/sku-attribute-code/{sku-attribute-code}', [SkuAttributeCodeController::class, 'show'])->middleware('permission:View_Vendor');
    Route::post('/sku-attribute-code', [SkuAttributeCodeController::class, 'store'])->middleware('permission:Create_Vendor');
    Route::put('/sku-attribute-code/{sku-attribute-code}', [SkuAttributeCodeController::class, 'update'])->middleware('permission:Update_Vendor');
    Route::delete('/sku-attribute-code/{sku-attribute-code}', [DeleteVendorController::class, 'delete'])->middleware('permission:Delete_Vendor');
});


// ========================================================
// Media Routes
// ========================================================
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::delete('/catalog/media/{media}', DeleteMediaController::class)->middleware('permission:Update_Product');
});
