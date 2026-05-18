<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Http\Controllers\Auth\ConfirmPasswordResetController;
use App\Http\Controllers\Customer\ListCustomersController;
use App\Http\Controllers\Shipping\ShippingZone\ListShippingZonesController;
use App\Http\Controllers\Shipping\ShippingZone\ShowShippingZoneController;
use App\Http\Controllers\Shipping\ShippingZone\CreateShippingZoneController;
use App\Http\Controllers\Shipping\ShippingZone\UpdateShippingZoneController;
use App\Http\Controllers\Shipping\ShippingZone\DeleteShippingZoneController;
use App\Http\Controllers\Shipping\ShippingMethod\ListShippingMethodsController;
use App\Http\Controllers\Shipping\ShippingMethod\ShowShippingMethodController;
use App\Http\Controllers\Shipping\ShippingMethod\CreateShippingMethodController;
use App\Http\Controllers\Shipping\ShippingMethod\UpdateShippingMethodController;
use App\Http\Controllers\Shipping\ShippingMethod\DeleteShippingMethodController;
use App\Http\Controllers\Shipping\ShippingRate\ListShippingRatesController;
use App\Http\Controllers\Shipping\ShippingRate\ShowShippingRateController;
use App\Http\Controllers\Shipping\ShippingRate\CreateShippingRateController;
use App\Http\Controllers\Shipping\ShippingRate\UpdateShippingRateController;
use App\Http\Controllers\Shipping\ShippingRate\DeleteShippingRateController;
use App\Http\Controllers\Shipping\Shipment\ListShipmentsController;
use App\Http\Controllers\Shipping\Shipment\ShowShipmentController;
use App\Http\Controllers\Shipping\Shipment\CreateShipmentController;
use App\Http\Controllers\Shipping\Shipment\UpdateShipmentController;
use App\Http\Controllers\Shipping\Shipment\DeleteShipmentController;
use App\Http\Controllers\Auth\CreateAccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RequestPasswordResetController;
use App\Http\Controllers\Auth\SelfController;
use App\Http\Controllers\Auth\VerifyPasswordResetCodeController;
use App\Http\Controllers\Collection\AttachProductsController;
use App\Http\Controllers\Collection\CreateCollectionController;
use App\Http\Controllers\Collection\DeleteCollectionController;
use App\Http\Controllers\Collection\DetachProductController;
use App\Http\Controllers\Collection\ListCollectionsController;
use App\Http\Controllers\Collection\ShowCollectionController;
use App\Http\Controllers\Collection\UpdateCollectionController;
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
use App\Http\Controllers\PaymentMethod\CreatePaymentMethodController;
use App\Http\Controllers\PaymentMethod\DeletePaymentMethodController;
use App\Http\Controllers\PaymentMethod\ListPaymentMethodsController;
use App\Http\Controllers\PaymentMethod\ShowPaymentMethodController;
use App\Http\Controllers\PaymentMethod\UpdatePaymentMethodController;
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
    Route::delete('/sku-attribute-code/{id}', [SkuAttributeCodeController::class, 'destroy'])->middleware('permission:Delete_Vendor');
});


// ========================================================
// COLLECTIONS API ROUTES
// ========================================================

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/collections', ListCollectionsController::class)->middleware('permission:View_Category');
    Route::get('/collections/{collection}', ShowCollectionController::class)->middleware('permission:View_Category');
    Route::post('/collections', CreateCollectionController::class)->middleware('permission:Create_Category');
    Route::put('/collections/{collection}', UpdateCollectionController::class)->middleware('permission:Update_Category');
    Route::delete('/collections/{collection}', DeleteCollectionController::class)->middleware('permission:Delete_Category');
    Route::post('/collections/{collection}/products', AttachProductsController::class)->middleware('permission:Update_Category');
    Route::delete('/collections/{collection}/products/{product}', DetachProductController::class)->middleware('permission:Update_Category');
});

// ========================================================
// CUSTOMERS API ROUTES
// ========================================================

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/customers', ListCustomersController::class)->middleware('permission:View_Customer');
});

// ========================================================
// SHIPPING API ROUTES
// ========================================================

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/shipping/zones', ListShippingZonesController::class)->middleware('permission:View_Shipment');
    Route::get('/shipping/zones/{shippingZone}', ShowShippingZoneController::class)->middleware('permission:View_Shipment');
    Route::post('/shipping/zones', CreateShippingZoneController::class)->middleware('permission:Create_Shipment');
    Route::put('/shipping/zones/{shippingZone}', UpdateShippingZoneController::class)->middleware('permission:Update_Shipment');
    Route::delete('/shipping/zones/{shippingZone}', DeleteShippingZoneController::class)->middleware('permission:Delete_Shipment');

    Route::get('/shipping/methods', ListShippingMethodsController::class)->middleware('permission:View_Shipment');
    Route::get('/shipping/methods/{shippingMethod}', ShowShippingMethodController::class)->middleware('permission:View_Shipment');
    Route::post('/shipping/methods', CreateShippingMethodController::class)->middleware('permission:Create_Shipment');
    Route::put('/shipping/methods/{shippingMethod}', UpdateShippingMethodController::class)->middleware('permission:Update_Shipment');
    Route::delete('/shipping/methods/{shippingMethod}', DeleteShippingMethodController::class)->middleware('permission:Delete_Shipment');

    Route::get('/shipping/rates', ListShippingRatesController::class)->middleware('permission:View_Shipment');
    Route::get('/shipping/rates/{shippingRate}', ShowShippingRateController::class)->middleware('permission:View_Shipment');
    Route::post('/shipping/rates', CreateShippingRateController::class)->middleware('permission:Create_Shipment');
    Route::put('/shipping/rates/{shippingRate}', UpdateShippingRateController::class)->middleware('permission:Update_Shipment');
    Route::delete('/shipping/rates/{shippingRate}', DeleteShippingRateController::class)->middleware('permission:Delete_Shipment');

    Route::get('/shipments', ListShipmentsController::class)->middleware('permission:View_Shipment');
    Route::get('/shipments/{shipment}', ShowShipmentController::class)->middleware('permission:View_Shipment');
    Route::post('/shipments', CreateShipmentController::class)->middleware('permission:Create_Shipment');
    Route::put('/shipments/{shipment}', UpdateShipmentController::class)->middleware('permission:Update_Shipment');
    Route::delete('/shipments/{shipment}', DeleteShipmentController::class)->middleware('permission:Delete_Shipment');
});

// ========================================================
// PAYMENT METHOD API ROUTES
// ========================================================

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/payment-methods', ListPaymentMethodsController::class)->middleware('permission:View_PaymentMethod');
    Route::get('/payment-methods/{paymentMethod}', ShowPaymentMethodController::class)->middleware('permission:View_PaymentMethod');
    Route::post('/payment-methods', CreatePaymentMethodController::class)->middleware('permission:Create_PaymentMethod');
    Route::put('/payment-methods/{paymentMethod}', UpdatePaymentMethodController::class)->middleware('permission:Update_PaymentMethod');
    Route::delete('/payment-methods/{paymentMethod}', DeletePaymentMethodController::class)->middleware('permission:Delete_PaymentMethod');
});

// ========================================================
// Media Routes
// ========================================================
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::delete('/catalog/media/{media}', DeleteMediaController::class)->middleware('permission:Update_Product');
});
