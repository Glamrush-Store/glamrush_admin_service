# GlamRush Admin API Routes

This document summarizes the routes declared in `routes/api.php`.

Base prefix: `/api/v1`

Most admin routes require Sanctum authentication with `auth:sanctum` and a permission middleware. Public/authentication exceptions are noted below.

## Auth

| Method | Path | Controller | Middleware |
| --- | --- | --- | --- |
| GET | `/health` | inline health check | none |
| POST | `/account/create` | `CreateAccountController` | `auth:sanctum`, `permission:Create_User` |
| POST | `/account/login` | `LoginController` | none |
| POST | `/account/logout` | `LogoutController` | `auth:sanctum` |
| POST | `/password/reset/request` | `RequestPasswordResetController` | none |
| POST | `/password/reset/verify` | `VerifyPasswordResetCodeController` | none |
| POST | `/password/reset/confirm` | `ConfirmPasswordResetController` | `auth:sanctum`, `ability:password:reset` |
| GET | `/whoami` | `SelfController` | `auth:sanctum` |

## Categories

All category routes require `auth:sanctum`.

| Method | Path | Controller | Permission |
| --- | --- | --- | --- |
| GET | `/categories` | `ListCategoriesController` | `View_Category` |
| GET | `/categories/{category}` | `ShowCategoryController` | `View_Category` |
| POST | `/categories` | `CreateCategoryController` | `Create_Category` |
| PUT | `/categories/{category}` | `UpdateCategoryController` | `Update_Category` |
| DELETE | `/categories/{category}` | `DeleteCategoryController` | `Delete_Category` |

## Brands

All brand routes require `auth:sanctum`.

| Method | Path | Controller | Permission |
| --- | --- | --- | --- |
| GET | `/brands` | `ListBrandsController` | `View_Brand` |
| GET | `/brands/{brand}` | `ShowBrandController` | `View_Brand` |
| POST | `/brands` | `CreateBrandController` | `Create_Brand` |
| PUT | `/brands/{brand}` | `UpdateBrandController` | `Update_Brand` |
| DELETE | `/brands/{brand}` | `DeleteBrandController` | `Delete_Brand` |

## Vendors

All vendor routes require `auth:sanctum`.

| Method | Path | Controller | Permission |
| --- | --- | --- | --- |
| GET | `/vendors` | `ListVendorController` | `View_Vendor` |
| GET | `/vendors/{vendor}` | `ShowVendorController` | `View_Vendor` |
| POST | `/vendors` | `CreateVendorController` | `Create_Vendor` |
| PUT | `/vendors/{vendor}` | `UpdateVendorController` | `Update_Vendor` |
| DELETE | `/vendors/{vendor}` | `DeleteVendorController` | `Delete_Vendor` |

## Products

All product routes require `auth:sanctum`.

| Method | Path | Controller | Permission |
| --- | --- | --- | --- |
| GET | `/products` | `ListProductsController` | `View_Product` |
| GET | `/products/{product}` | `ShowProductController` | `View_Product` |
| POST | `/products` | `CreateProductController` | `Create_Product` |
| PUT | `/products/{product}` | `UpdateProductController` | `Update_Product` |
| DELETE | `/products/{product}` | `DeleteProductController` | `Delete_Product` |

## Product Variants

All product variant routes require `auth:sanctum`.

| Method | Path | Controller | Permission |
| --- | --- | --- | --- |
| GET | `/productvariants/{variant}` | `ShowProductVariantController` | `View_Product` |
| PUT | `/productvariants/{variant}` | `UpdateProductVariantController` | `Update_Product` |
| DELETE | `/productvariants/{variant}` | `DeleteProductVariantController` | `Delete_Product` |

## SKU Attribute Codes

All SKU attribute code routes require `auth:sanctum`.

| Method | Path | Controller Action | Permission |
| --- | --- | --- | --- |
| GET | `/sku-attribute-code` | `SkuAttributeCodeController@index` | `View_Vendor` |
| GET | `/sku-attribute-code/list/types` | `SkuAttributeCodeController@types` | `View_Vendor` |
| GET | `/sku-attribute-code/{sku-attribute-code}` | `SkuAttributeCodeController@show` | `View_Vendor` |
| POST | `/sku-attribute-code` | `SkuAttributeCodeController@store` | `Create_Vendor` |
| PUT | `/sku-attribute-code/{sku-attribute-code}` | `SkuAttributeCodeController@update` | `Update_Vendor` |
| DELETE | `/sku-attribute-code/{id}` | `SkuAttributeCodeController@destroy` | `Delete_Vendor` |

## Collections

All collection routes require `auth:sanctum`.

| Method | Path | Controller | Permission |
| --- | --- | --- | --- |
| GET | `/collections` | `ListCollectionsController` | `View_Category` |
| GET | `/collections/{collection}` | `ShowCollectionController` | `View_Category` |
| POST | `/collections` | `CreateCollectionController` | `Create_Category` |
| PUT | `/collections/{collection}` | `UpdateCollectionController` | `Update_Category` |
| DELETE | `/collections/{collection}` | `DeleteCollectionController` | `Delete_Category` |
| POST | `/collections/{collection}/products` | `AttachProductsController` | `Update_Category` |
| DELETE | `/collections/{collection}/products/{product}` | `DetachProductController` | `Update_Category` |

## Customers

All customer routes require `auth:sanctum`.

| Method | Path | Controller | Permission |
| --- | --- | --- | --- |
| GET | `/customers` | `ListCustomersController` | `View_Customer` |

## Shipping Zones

All shipping zone routes require `auth:sanctum`.

| Method | Path | Controller | Permission |
| --- | --- | --- | --- |
| GET | `/shipping/zones` | `ListShippingZonesController` | `View_Shipment` |
| GET | `/shipping/zones/{shippingZone}` | `ShowShippingZoneController` | `View_Shipment` |
| POST | `/shipping/zones` | `CreateShippingZoneController` | `Create_Shipment` |
| PUT | `/shipping/zones/{shippingZone}` | `UpdateShippingZoneController` | `Update_Shipment` |
| DELETE | `/shipping/zones/{shippingZone}` | `DeleteShippingZoneController` | `Delete_Shipment` |

## Shipping Methods

All shipping method routes require `auth:sanctum`.

| Method | Path | Controller | Permission |
| --- | --- | --- | --- |
| GET | `/shipping/methods` | `ListShippingMethodsController` | `View_Shipment` |
| GET | `/shipping/methods/{shippingMethod}` | `ShowShippingMethodController` | `View_Shipment` |
| POST | `/shipping/methods` | `CreateShippingMethodController` | `Create_Shipment` |
| PUT | `/shipping/methods/{shippingMethod}` | `UpdateShippingMethodController` | `Update_Shipment` |
| DELETE | `/shipping/methods/{shippingMethod}` | `DeleteShippingMethodController` | `Delete_Shipment` |

## Shipping Rates

All shipping rate routes require `auth:sanctum`.

| Method | Path | Controller | Permission |
| --- | --- | --- | --- |
| GET | `/shipping/rates` | `ListShippingRatesController` | `View_Shipment` |
| GET | `/shipping/rates/{shippingRate}` | `ShowShippingRateController` | `View_Shipment` |
| POST | `/shipping/rates` | `CreateShippingRateController` | `Create_Shipment` |
| PUT | `/shipping/rates/{shippingRate}` | `UpdateShippingRateController` | `Update_Shipment` |
| DELETE | `/shipping/rates/{shippingRate}` | `DeleteShippingRateController` | `Delete_Shipment` |

## Shipments

All shipment routes require `auth:sanctum`.

| Method | Path | Controller | Permission |
| --- | --- | --- | --- |
| GET | `/shipments` | `ListShipmentsController` | `View_Shipment` |
| GET | `/shipments/{shipment}` | `ShowShipmentController` | `View_Shipment` |
| POST | `/shipments` | `CreateShipmentController` | `Create_Shipment` |
| PUT | `/shipments/{shipment}` | `UpdateShipmentController` | `Update_Shipment` |
| DELETE | `/shipments/{shipment}` | `DeleteShipmentController` | `Delete_Shipment` |

## Media

All media routes require `auth:sanctum`.

| Method | Path | Controller | Permission |
| --- | --- | --- | --- |
| DELETE | `/catalog/media/{media}` | `DeleteMediaController` | `Update_Product` |
