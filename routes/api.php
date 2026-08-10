<?php

/*
 * (c) 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Http\Controllers\AccessControl\RoleController;
use App\Http\Controllers\AccessControl\UserController;
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
use App\Http\Controllers\Collection\AttachProductsController;
use App\Http\Controllers\Collection\CreateCollectionController;
use App\Http\Controllers\Collection\DeleteCollectionController;
use App\Http\Controllers\Collection\DetachProductController;
use App\Http\Controllers\Collection\ListCollectionsController;
use App\Http\Controllers\Collection\ShowCollectionController;
use App\Http\Controllers\Collection\UpdateCollectionController;
use App\Http\Controllers\Content\ContentPageController;
use App\Http\Controllers\Content\FaqCategoryController;
use App\Http\Controllers\Content\FaqController;
use App\Http\Controllers\Customer\ListCustomersController;
use App\Http\Controllers\Dashboard\ShowDashboardAnalyticsController;
use App\Http\Controllers\Discount\DiscountCodeController;
use App\Http\Controllers\Media\DeleteMediaController;
use App\Http\Controllers\Newsletter\ExportNewsletterSubscribersController;
use App\Http\Controllers\Newsletter\ListNewsletterSubscribersController;
use App\Http\Controllers\Newsletter\ShowNewsletterSubscriberController;
use App\Http\Controllers\Order\CreateManualOrderController;
use App\Http\Controllers\Order\ListOrdersController;
use App\Http\Controllers\Order\ShowOrderController;
use App\Http\Controllers\Order\UpdateOrderStatusController;
use App\Http\Controllers\Order\UpdateOrderStatusesController;
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
use App\Http\Controllers\Shipping\Shipment\CreateShipmentController;
use App\Http\Controllers\Shipping\Shipment\DeleteShipmentController;
use App\Http\Controllers\Shipping\Shipment\ListShipmentsController;
use App\Http\Controllers\Shipping\Shipment\ShowShipmentController;
use App\Http\Controllers\Shipping\Shipment\UpdateShipmentController;
use App\Http\Controllers\Shipping\ShippingMethod\CreateShippingMethodController;
use App\Http\Controllers\Shipping\ShippingMethod\DeleteShippingMethodController;
use App\Http\Controllers\Shipping\ShippingMethod\ListShippingMethodsController;
use App\Http\Controllers\Shipping\ShippingMethod\ShowShippingMethodController;
use App\Http\Controllers\Shipping\ShippingMethod\UpdateShippingMethodController;
use App\Http\Controllers\Shipping\ShippingRate\CreateShippingRateController;
use App\Http\Controllers\Shipping\ShippingRate\DeleteShippingRateController;
use App\Http\Controllers\Shipping\ShippingRate\ListShippingRatesController;
use App\Http\Controllers\Shipping\ShippingRate\ShowShippingRateController;
use App\Http\Controllers\Shipping\ShippingRate\UpdateShippingRateController;
use App\Http\Controllers\Shipping\ShippingZone\CreateShippingZoneController;
use App\Http\Controllers\Shipping\ShippingZone\DeleteShippingZoneController;
use App\Http\Controllers\Shipping\ShippingZone\ListShippingZonesController;
use App\Http\Controllers\Shipping\ShippingZone\ShowShippingZoneController;
use App\Http\Controllers\Shipping\ShippingZone\UpdateShippingZoneController;
use App\Http\Controllers\SkuAttributeCode\SkuAttributeCodeController;
use App\Http\Controllers\Storefront\PublishedStorefrontHomepageController;
use App\Http\Controllers\Storefront\StorefrontCampaignController;
use App\Http\Controllers\Storefront\StorefrontHomepageSectionController;
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
// ADMIN USERS, ROLES AND PERMISSIONS
// ========================================================
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/permissions', [RoleController::class, 'permissions'])->middleware('permission:ViewAny_Role');

    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:ViewAny_Role');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:Create_Role');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->middleware('permission:View_Role');
    Route::patch('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:Update_Role');
    Route::put('/roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->middleware('permission:Update_Role');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:Delete_Role');

    Route::get('/users', [UserController::class, 'index'])->middleware('permission:ViewAny_User');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:Create_User');
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('permission:View_User');
    Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('permission:Update_User');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:Delete_User');
});

// ========================================================
// DASHBOARD ANALYTICS
// ========================================================
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/analytics', ShowDashboardAnalyticsController::class)->middleware('permission:View_Dashboard');
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
// CONTENT PAGES AND FAQ MANAGEMENT
// ========================================================
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/content-pages', [ContentPageController::class, 'index'])->middleware('permission:View_ContentPage');
    Route::post('/content-pages', [ContentPageController::class, 'store'])->middleware('permission:Create_ContentPage');
    Route::get('/content-pages/{contentPage}', [ContentPageController::class, 'show'])->middleware('permission:View_ContentPage');
    Route::patch('/content-pages/{contentPage}', [ContentPageController::class, 'update'])->middleware('permission:Update_ContentPage');
    Route::post('/content-pages/{contentPage}/publish', [ContentPageController::class, 'publish'])->middleware('permission:Publish_ContentPage');
    Route::post('/content-pages/{contentPage}/unpublish', [ContentPageController::class, 'unpublish'])->middleware('permission:Unpublish_ContentPage');
    Route::post('/content-pages/{contentPage}/duplicate', [ContentPageController::class, 'duplicate'])->middleware('permission:Duplicate_ContentPage');
    Route::delete('/content-pages/{contentPage}', [ContentPageController::class, 'destroy'])->middleware('permission:Delete_ContentPage');

    Route::get('/faq-categories', [FaqCategoryController::class, 'index'])->middleware('permission:View_FaqCategory');
    Route::post('/faq-categories', [FaqCategoryController::class, 'store'])->middleware('permission:Create_FaqCategory');
    Route::post('/faq-categories/reorder', [FaqCategoryController::class, 'reorder'])->name('faq-categories.reorder')->middleware('permission:Reorder_FaqCategory');
    Route::get('/faq-categories/{faqCategory}', [FaqCategoryController::class, 'show'])->middleware('permission:View_FaqCategory');
    Route::patch('/faq-categories/{faqCategory}', [FaqCategoryController::class, 'update'])->middleware('permission:Update_FaqCategory');
    Route::delete('/faq-categories/{faqCategory}', [FaqCategoryController::class, 'destroy'])->middleware('permission:Delete_FaqCategory');

    Route::get('/faqs', [FaqController::class, 'index'])->middleware('permission:View_Faq');
    Route::post('/faqs', [FaqController::class, 'store'])->middleware('permission:Create_Faq');
    Route::post('/faqs/reorder', [FaqController::class, 'reorder'])->name('faqs.reorder')->middleware('permission:Reorder_Faq');
    Route::get('/faqs/{faq}', [FaqController::class, 'show'])->middleware('permission:View_Faq');
    Route::patch('/faqs/{faq}', [FaqController::class, 'update'])->middleware('permission:Update_Faq');
    Route::post('/faqs/{faq}/publish', [FaqController::class, 'publish'])->middleware('permission:Publish_Faq');
    Route::post('/faqs/{faq}/unpublish', [FaqController::class, 'unpublish'])->middleware('permission:Unpublish_Faq');
    Route::post('/faqs/{faq}/duplicate', [FaqController::class, 'duplicate'])->middleware('permission:Duplicate_Faq');
    Route::delete('/faqs/{faq}', [FaqController::class, 'destroy'])->middleware('permission:Delete_Faq');
});

// ========================================================
// DISCOUNT CODE MANAGEMENT
// ========================================================
Route::prefix('v1/discount-codes')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [DiscountCodeController::class, 'index'])->middleware('permission:View_Discount');
    Route::post('/', [DiscountCodeController::class, 'store'])->middleware('permission:Create_Discount');
    Route::get('/{discountCode}', [DiscountCodeController::class, 'show'])->middleware('permission:View_Discount');
    Route::patch('/{discountCode}', [DiscountCodeController::class, 'update'])->middleware('permission:Update_Discount');
    Route::post('/{discountCode}/activate', [DiscountCodeController::class, 'activate'])->middleware('permission:Activate_Discount');
    Route::post('/{discountCode}/deactivate', [DiscountCodeController::class, 'deactivate'])->middleware('permission:Deactivate_Discount');
    Route::post('/{discountCode}/duplicate', [DiscountCodeController::class, 'duplicate'])->middleware('permission:Duplicate_Discount');
});

// ========================================================
// STOREFRONT HOMEPAGE MERCHANDISING
// ========================================================
Route::prefix('v1/storefronts/{storefront}')->middleware('auth:sanctum')->group(function () {
    Route::get('/campaigns', [StorefrontCampaignController::class, 'index'])->middleware('permission:ViewAny_StorefrontCampaign');
    Route::post('/campaigns', [StorefrontCampaignController::class, 'store'])->middleware('permission:Create_StorefrontCampaign');
    Route::get('/campaigns/{campaign}', [StorefrontCampaignController::class, 'show'])->middleware('permission:View_StorefrontCampaign');
    Route::put('/campaigns/{campaign}', [StorefrontCampaignController::class, 'update'])->middleware('permission:Update_StorefrontCampaign');
    Route::delete('/campaigns/{campaign}', [StorefrontCampaignController::class, 'destroy'])->middleware('permission:Delete_StorefrontCampaign');
    Route::patch('/campaigns/{campaign}/enable', [StorefrontCampaignController::class, 'enable'])->middleware('permission:Update_StorefrontCampaign');
    Route::patch('/campaigns/{campaign}/disable', [StorefrontCampaignController::class, 'disable'])->middleware('permission:Update_StorefrontCampaign');

    Route::get('/homepage-sections', [StorefrontHomepageSectionController::class, 'index'])->middleware('permission:ViewAny_StorefrontHomepageSection');
    Route::post('/homepage-sections', [StorefrontHomepageSectionController::class, 'store'])->middleware('permission:Create_StorefrontHomepageSection');
    Route::put('/homepage-sections/reorder', [StorefrontHomepageSectionController::class, 'reorder'])->middleware('permission:Update_StorefrontHomepageSection');
    Route::get('/homepage-sections/{section}', [StorefrontHomepageSectionController::class, 'show'])->middleware('permission:View_StorefrontHomepageSection');
    Route::put('/homepage-sections/{section}', [StorefrontHomepageSectionController::class, 'update'])->middleware('permission:Update_StorefrontHomepageSection');
    Route::delete('/homepage-sections/{section}', [StorefrontHomepageSectionController::class, 'destroy'])->middleware('permission:Delete_StorefrontHomepageSection');
    Route::patch('/homepage-sections/{section}/enable', [StorefrontHomepageSectionController::class, 'enable'])->middleware('permission:Update_StorefrontHomepageSection');
    Route::patch('/homepage-sections/{section}/disable', [StorefrontHomepageSectionController::class, 'disable'])->middleware('permission:Update_StorefrontHomepageSection');
});

Route::get('/internal/v1/storefronts/{storefront}/homepage', PublishedStorefrontHomepageController::class)
    ->middleware('internal-service');

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
// NEWSLETTER SUBSCRIBERS API ROUTES
// ========================================================

Route::prefix('v1/newsletter/subscribers')->middleware('auth:sanctum')->group(function () {
    Route::get('/', ListNewsletterSubscribersController::class)->middleware('permission:ViewAny_NewsletterSubscriber');
    Route::get('/export', ExportNewsletterSubscribersController::class)
        ->middleware(['permission:Export_NewsletterSubscriber', 'throttle:5,1']);
    Route::get('/{subscriber}', ShowNewsletterSubscriberController::class)->middleware('permission:View_NewsletterSubscriber');
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
// ORDERS API ROUTES
// ========================================================

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/orders', ListOrdersController::class)->middleware('permission:View_Order');
    Route::post('/orders/manual', CreateManualOrderController::class)->middleware('permission:Create_Order');
    Route::get('/orders/{order}', ShowOrderController::class)->middleware('permission:View_Order');
    Route::patch('/orders/{order}/status', UpdateOrderStatusController::class)->middleware('permission:Update_Order');
    Route::patch('/orders/{order}/statuses', UpdateOrderStatusesController::class)->middleware('permission:Update_Order');
});
// ========================================================
// Media Routes
// ========================================================
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::delete('/catalog/media/{media}', DeleteMediaController::class)->middleware('permission:Update_Product');
});

// ========================================================
// Liveliness Test Route
// ========================================================
Route::prefix('v1')->group(function () {
    Route::get('/up', fn () => response()->json(['status' => 'ok']));
});

