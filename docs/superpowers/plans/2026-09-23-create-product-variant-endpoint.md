# Create Product Variant Endpoint Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Allow an administrator to add a variant to an existing variable product without replacing existing variants.

**Architecture:** Add a nested product-variant create endpoint backed by a dedicated form request and transactional use case. Reuse SKU generation, variant persistence, media upload, and product cache invalidation already used during product creation while adding duplicate-combination and default-variant safeguards.

**Tech Stack:** PHP 8.2, Laravel 12, Pest 4, Eloquent, Spatie Media Library

**Spec:** User request in the active Codex task dated 2026-09-23.

## Global Constraints

- The endpoint must append one variant and preserve all existing variant IDs and media.
- Only products with `type=variable` may receive additional variants.
- The server generates the SKU from the product SKU and submitted attributes.
- Duplicate attribute combinations and generated SKUs return HTTP 409.
- Variant status values are `active` and `disabled`.
- The existing `Update_Product` permission protects the endpoint.

---

### Task 1: Endpoint Contract And Behavior

**Files:**
- Create: `app/Http/Requests/ProductVariant/CreateProductVariantRequest.php`
- Create: `app/Http/Controllers/ProductVariant/CreateProductVariantController.php`
- Create: `app/Domain/Product/ProductVariant/UseCases/CreateProductVariantUseCase.php`
- Modify: `routes/api.php`
- Modify: `app/Http/Requests/ProductVariant/UpdateProductVariantRequest.php`
- Test: `tests/Feature/ProductVariant/CreateProductVariantTest.php`

**Interfaces:**
- Consumes: `Product`, `CreateProductVariantsAction::run()`, `GenerateVariantSkuAction::run()`, `UploadVariantPhotosAction::run()`.
- Produces: `POST /api/v1/products/{product}/variants` returning `ProductVariantResource` with HTTP 201.

- [x] **Step 1: Write endpoint feature tests**

Cover successful append, generated SKU, preservation of existing variants, default reassignment, simple-product rejection, duplicate attributes, validation, permissions, and product-saved event dispatch.

- [x] **Step 2: Run the focused test and verify it fails**

Run: `php artisan test tests/Feature/ProductVariant/CreateProductVariantTest.php`

Expected: FAIL because the route and create classes do not exist.

- [x] **Step 3: Add request validation**

Validate `price`, optional sale dates and price, stock controls, a non-empty list of `{type,value}` attributes with distinct types, optional sort order/status/default flag, and up to two images. Normalize omitted booleans and status to the database defaults in the use case rather than trusting UI defaults.

- [x] **Step 4: Add transactional create behavior**

Reject non-variable products; canonicalize attribute pairs by type and value for duplicate comparison; generate the SKU; reject duplicate attributes or SKU with `BusinessException` 409; unset an existing default only when the new variant is explicitly default; create the variant; upload photos; dispatch `ProductSavedEvent`; return the fresh variant with media.

- [x] **Step 5: Register the nested route and controller**

Register `POST /api/v1/products/{product}/variants` before the product wildcard is able to conflict and protect it with `auth:sanctum` plus `permission:Update_Product`. Return message `Product variant created` and HTTP 201.

- [x] **Step 6: Align update status validation**

Change the existing update validator from `active|inactive` to `active|disabled`, matching inventory checks and stored values.

- [x] **Step 7: Run focused and related tests**

Run: `php artisan test tests/Feature/ProductVariant/CreateProductVariantTest.php tests/Integration/Product/CreateProductUseCaseTest.php`

Expected: PASS.

- [x] **Step 8: Run formatting and route verification**

Run: `vendor/bin/pint --test app/Domain/Product/ProductVariant app/Http/Controllers/ProductVariant app/Http/Requests/ProductVariant tests/Feature/ProductVariant routes/api.php`

Run: `php artisan route:list --path=api/v1/products`

Expected: formatting passes and the new POST route is listed with `Update_Product` authorization.
